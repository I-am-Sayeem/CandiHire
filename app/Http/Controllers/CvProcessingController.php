<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CvProcessingController extends Controller
{
    private $uploadPath = 'cv_processing';
    
    /**
     * Upload CV files
     */
    public function upload(Request $request)
    {
        try {
            $companyId = session('user_id');
            
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }
            
            if (!$request->hasFile('cvs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files uploaded'
                ], 400);
            }
            
            $jobPosition = $request->input('jobPosition', '');
            $experienceLevel = $request->input('experienceLevel', 'any');
            $requiredSkills = $request->input('requiredSkills', '');
            $customCriteria = $request->input('customCriteria', '');
            
            if (empty($jobPosition)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job position is required'
                ], 400);
            }
            
            // Generate processing ID
            $processingId = Str::uuid()->toString();
            
            // Create upload directory
            $uploadDir = $this->uploadPath . '/' . $companyId . '/' . $processingId;
            Storage::disk('public')->makeDirectory($uploadDir);
            
            $uploadedFiles = [];
            $files = $request->file('cvs');
            
            // Handle single or multiple files
            if (!is_array($files)) {
                $files = [$files];
            }
            
            foreach ($files as $file) {
                // Validate file
                if ($file->getClientOriginalExtension() !== 'pdf') {
                    continue;
                }
                
                if ($file->getSize() > 10 * 1024 * 1024) { // 10MB limit
                    continue;
                }
                
                // Generate safe filename
                $originalName = $file->getClientOriginalName();
                $storedName = uniqid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';
                
                // Store file
                $path = $file->storeAs($uploadDir, $storedName, 'public');
                
                if ($path) {
                    $uploadedFiles[] = [
                        'originalName' => $originalName,
                        'storedName' => $storedName,
                        'path' => $path,
                        'size' => $file->getSize()
                    ];
                }
            }
            
            if (empty($uploadedFiles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid PDF files uploaded'
                ], 400);
            }
            
            // Store processing data in session for now (could be database in production)
            session([
                'cv_processing_' . $processingId => [
                    'companyId' => $companyId,
                    'jobPosition' => $jobPosition,
                    'experienceLevel' => $experienceLevel,
                    'requiredSkills' => $requiredSkills,
                    'customCriteria' => $customCriteria,
                    'files' => $uploadedFiles,
                    'candidates' => []
                ]
            ]);
            
            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' CV file(s) uploaded successfully',
                'processingId' => $processingId,
                'uploadedFiles' => $uploadedFiles
            ]);
            
        } catch (\Exception $e) {
            Log::error('CV Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process uploaded CVs - extract text and parse candidate info
     */
    public function process(Request $request)
    {
        try {
            $companyId = session('user_id');
            $processingId = $request->input('processingId');
            
            if (!$processingId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processing ID required'
                ], 400);
            }
            
            $processingData = session('cv_processing_' . $processingId);
            
            if (!$processingData || $processingData['companyId'] != $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processing record not found'
                ], 404);
            }
            
            $candidates = [];
            
            foreach ($processingData['files'] as $index => $file) {
                $fullPath = storage_path('app/public/' . $file['path']);
                
                if (!file_exists($fullPath)) {
                    Log::warning('CV file not found: ' . $fullPath);
                    continue;
                }
                
                // Extract text from PDF
                $pdfText = $this->extractTextFromPDF($fullPath);
                
                // Parse candidate data
                $candidateData = $this->parseCVText($pdfText, $file['originalName']);
                
                // Calculate match percentage
                $matchPercentage = $this->calculateMatchPercentage(
                    $candidateData,
                    $processingData['jobPosition'],
                    $processingData['requiredSkills'],
                    $processingData['experienceLevel']
                );
                
                $candidates[] = [
                    'id' => $index + 1,
                    'fileId' => $index,
                    'name' => $candidateData['name'],
                    'email' => $candidateData['email'],
                    'phone' => $candidateData['phone'],
                    'linkedin' => $candidateData['linkedin'],
                    'location' => $candidateData['location'],
                    'experienceYears' => $candidateData['experienceYears'],
                    'education' => $candidateData['education'],
                    'skills' => $candidateData['skills'],
                    'summary' => $candidateData['summary'],
                    'match' => $matchPercentage,
                    'fileName' => $file['originalName'],
                    'extractionStatus' => !empty($pdfText) ? 'success' : 'partial'
                ];
            }
            
            // Sort by match percentage (highest first)
            usort($candidates, function($a, $b) {
                return $b['match'] - $a['match'];
            });
            
            // Update session with candidates
            $processingData['candidates'] = $candidates;
            session(['cv_processing_' . $processingId => $processingData]);
            
            return response()->json([
                'success' => true,
                'message' => 'CVs processed successfully. Found ' . count($candidates) . ' candidate(s).',
                'candidates' => $candidates
            ]);
            
        } catch (\Exception $e) {
            Log::error('CV Processing Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply filters to processed candidates
     */
    public function filter(Request $request)
    {
        try {
            $companyId = session('user_id');
            $processingId = $request->input('processingId');
            $requiredSkills = $request->input('requiredSkills', '');
            $experienceLevel = $request->input('experienceLevel', 'any');
            $minMatch = $request->input('minMatch', 0);
            
            $processingData = session('cv_processing_' . $processingId);
            
            if (!$processingData || $processingData['companyId'] != $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processing record not found'
                ], 404);
            }
            
            $candidates = $processingData['candidates'];
            
            // Filter by minimum match percentage
            if ($minMatch > 0) {
                $candidates = array_filter($candidates, function($c) use ($minMatch) {
                    return $c['match'] >= $minMatch;
                });
            }
            
            // Filter by experience level
            if ($experienceLevel !== 'any') {
                $candidates = array_filter($candidates, function($c) use ($experienceLevel) {
                    $years = $c['experienceYears'];
                    switch ($experienceLevel) {
                        case 'entry':
                            return $years <= 2;
                        case 'mid':
                            return $years >= 2 && $years <= 5;
                        case 'senior':
                            return $years >= 5;
                        default:
                            return true;
                    }
                });
            }
            
            // Re-calculate match with new skills
            if (!empty($requiredSkills)) {
                $skillsArray = array_map('trim', explode(',', $requiredSkills));
                foreach ($candidates as &$candidate) {
                    $candidate['match'] = $this->calculateMatchPercentage(
                        $candidate,
                        $processingData['jobPosition'],
                        $requiredSkills,
                        $experienceLevel
                    );
                }
            }
            
            // Sort by match
            usort($candidates, function($a, $b) {
                return $b['match'] - $a['match'];
            });
            
            return response()->json([
                'success' => true,
                'candidates' => array_values($candidates)
            ]);
            
        } catch (\Exception $e) {
            Log::error('CV Filter Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Filter failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Extract text from PDF file
     */
    private function extractTextFromPDF($filePath)
    {
        if (!file_exists($filePath)) {
            return '';
        }
        
        $text = '';
        
        // Method 1: Try pdftotext command (most reliable)
        if (function_exists('shell_exec')) {
            $escapedPath = escapeshellarg($filePath);
            $result = @shell_exec("pdftotext -layout $escapedPath - 2>/dev/null");
            if (!empty(trim($result))) {
                return trim($result);
            }
        }
        
        // Method 2: Read PDF content directly and extract text
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return '';
        }
        
        // Extract text between BT and ET markers (PDF text objects)
        if (preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $btMatches)) {
            foreach ($btMatches[1] as $textBlock) {
                $extracted = $this->extractTextFromPDFBlock($textBlock);
                if (!empty($extracted)) {
                    $text .= $extracted . ' ';
                }
            }
        }
        
        // Extract from Tj operator (single strings)
        if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $content, $tjMatches)) {
            foreach ($tjMatches[1] as $match) {
                $decoded = $this->decodePDFString($match);
                if (strlen($decoded) > 1) {
                    $text .= $decoded . ' ';
                }
            }
        }
        
        // Extract from TJ operator (arrays)
        if (preg_match_all('/\[((?:[^\[\]\\\\]|\\\\.)*)\]\s*TJ/s', $content, $tjArrayMatches)) {
            foreach ($tjArrayMatches[1] as $match) {
                if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/', $match, $arrayStrings)) {
                    foreach ($arrayStrings[1] as $str) {
                        $decoded = $this->decodePDFString($str);
                        if (strlen($decoded) > 1) {
                            $text .= $decoded . ' ';
                        }
                    }
                }
            }
        }
        
        // Clean extracted text
        $text = $this->cleanExtractedText($text);
        
        // Fallback: extract readable text
        if (empty(trim($text))) {
            $text = $this->extractReadableText($content);
        }
        
        return trim($text);
    }
    
    /**
     * Extract text from PDF text block
     */
    private function extractTextFromPDFBlock($block)
    {
        $text = '';
        
        // Extract from Tj operator
        if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/', $block, $matches)) {
            foreach ($matches[1] as $match) {
                $text .= $this->decodePDFString($match) . ' ';
            }
        }
        
        // Extract from TJ operator
        if (preg_match_all('/\[((?:[^\[\]])*)\]\s*TJ/', $block, $matches)) {
            foreach ($matches[1] as $match) {
                if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/', $match, $strings)) {
                    foreach ($strings[1] as $str) {
                        $text .= $this->decodePDFString($str) . ' ';
                    }
                }
            }
        }
        
        return $text;
    }
    
    /**
     * Decode PDF string escape sequences
     */
    private function decodePDFString($str)
    {
        $replacements = [
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\(' => '(',
            '\\)' => ')',
            '\\\\' => '\\',
        ];
        
        foreach ($replacements as $search => $replace) {
            $str = str_replace($search, $replace, $str);
        }
        
        // Handle octal escape sequences
        $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function($matches) {
            return chr(octdec($matches[1]));
        }, $str);
        
        return $str;
    }
    
    /**
     * Clean extracted text
     */
    private function cleanExtractedText($text)
    {
        // Remove non-printable characters except newlines
        $text = preg_replace('/[^\x20-\x7E\x0A\x0D\xA0-\xFF]/u', ' ', $text);
        
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Extract readable text as fallback
     */
    private function extractReadableText($content)
    {
        $text = '';
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            if (preg_match('/[A-Za-z]{3,}/', $line)) {
                if (preg_match_all('/[A-Za-z][A-Za-z0-9\s@.\-_,;:]{2,}/', $line, $matches)) {
                    foreach ($matches[0] as $match) {
                        $cleaned = trim($match);
                        if (strlen($cleaned) >= 3) {
                            $text .= $cleaned . ' ';
                        }
                    }
                }
            }
        }
        
        return $text;
    }
    
    /**
     * Parse CV text to extract candidate information
     */
    private function parseCVText($text, $fileName)
    {
        $data = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'linkedin' => '',
            'location' => '',
            'experienceYears' => 0,
            'education' => '',
            'skills' => [],
            'summary' => ''
        ];
        
        // Extract email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $emailMatch)) {
            $data['email'] = $emailMatch[0];
        }
        
        // Extract phone number (multiple formats)
        $phonePatterns = [
            '/(?:\+88)?[\s-]?01[3-9][\s-]?\d{2}[\s-]?\d{2}[\s-]?\d{2}[\s-]?\d{2}/', // Bangladesh
            '/(?:\+?1)?[-.\s]?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/', // US
            '/\+?[\d\s.-]{10,15}/' // Generic
        ];
        
        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $text, $phoneMatch)) {
                $phone = preg_replace('/[^\d+]/', '', $phoneMatch[0]);
                if (strlen($phone) >= 10) {
                    $data['phone'] = $phoneMatch[0];
                    break;
                }
            }
        }
        
        // Extract LinkedIn
        if (preg_match('/linkedin\.com\/in\/([a-zA-Z0-9_-]+)/', $text, $linkedinMatch)) {
            $data['linkedin'] = 'https://linkedin.com/in/' . $linkedinMatch[1];
        }
        
        // Extract name - usually first non-empty line or before email
        $lines = explode("\n", trim($text));
        foreach ($lines as $line) {
            $line = trim($line);
            // Name is usually 2-4 capitalized words
            if (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})$/', $line, $nameMatch)) {
                $data['name'] = $nameMatch[1];
                break;
            }
        }
        
        // Fallback: extract name from filename
        if (empty($data['name'])) {
            $data['name'] = $this->extractNameFromFileName($fileName);
        }
        
        // Extract experience years
        $expPatterns = [
            '/(\d+)\+?\s*(?:years?|yrs?)[\s.]*(?:of)?\s*(?:experience|exp)/i',
            '/experience\s*[:-]?\s*(\d+)\+?\s*(?:years?|yrs?)/i',
            '/(\d+)\+?\s*years?\s+(?:in|of|as)/i'
        ];
        
        foreach ($expPatterns as $pattern) {
            if (preg_match($pattern, $text, $expMatch)) {
                $data['experienceYears'] = (int)$expMatch[1];
                break;
            }
        }
        
        // Extract skills (common tech skills)
        $commonSkills = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Go', 'Rust', 'Swift',
            'React', 'Angular', 'Vue', 'Node.js', 'Laravel', 'Django', 'Spring', 'Express',
            'HTML', 'CSS', 'SASS', 'TypeScript', 'jQuery', 'Bootstrap', 'Tailwind',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Oracle', 'SQL Server', 'SQLite',
            'AWS', 'Azure', 'GCP', 'Docker', 'Kubernetes', 'Git', 'Linux', 'DevOps',
            'REST', 'API', 'GraphQL', 'Microservices', 'Agile', 'Scrum',
            'Machine Learning', 'AI', 'Data Science', 'TensorFlow', 'PyTorch',
            'Photoshop', 'Figma', 'Sketch', 'UI/UX', 'Illustrator'
        ];
        
        $textLower = strtolower($text);
        foreach ($commonSkills as $skill) {
            if (stripos($text, $skill) !== false) {
                $data['skills'][] = $skill;
            }
        }
        
        // Extract education
        $eduPatterns = [
            '/(?:BSc|Bachelor|B\.S\.|BS)[\s.]*(?:in|of)?\s*([A-Za-z\s]+)/i',
            '/(?:MSc|Master|M\.S\.|MS)[\s.]*(?:in|of)?\s*([A-Za-z\s]+)/i',
            '/(?:PhD|Ph\.D\.|Doctorate)[\s.]*(?:in|of)?\s*([A-Za-z\s]+)/i'
        ];
        
        foreach ($eduPatterns as $pattern) {
            if (preg_match($pattern, $text, $eduMatch)) {
                $data['education'] = trim($eduMatch[0]);
                break;
            }
        }
        
        // Extract location (common cities)
        $cities = ['Dhaka', 'Chittagong', 'Sylhet', 'Khulna', 'Rajshahi', 'Comilla', 
                   'New York', 'London', 'Singapore', 'Dubai', 'San Francisco', 'Toronto'];
        foreach ($cities as $city) {
            if (stripos($text, $city) !== false) {
                $data['location'] = $city;
                break;
            }
        }
        
        // Extract summary (first paragraph with reasonable length)
        if (preg_match('/(?:summary|profile|about|objective)[:\s]*(.{50,300}?)(?:\n\n|$)/is', $text, $summaryMatch)) {
            $data['summary'] = trim($summaryMatch[1]);
        }
        
        return $data;
    }
    
    /**
     * Extract name from filename
     */
    private function extractNameFromFileName($fileName)
    {
        // Remove extension
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Remove common prefixes/suffixes
        $name = preg_replace('/(cv|resume|curriculum|vitae|_cv|_resume|-cv|-resume)/i', '', $name);
        
        // Replace underscores and dashes with spaces
        $name = str_replace(['_', '-'], ' ', $name);
        
        // Remove numbers and special chars
        $name = preg_replace('/[0-9]+/', '', $name);
        
        // Title case
        $name = ucwords(strtolower(trim($name)));
        
        return $name ?: 'Unknown Candidate';
    }
    
    /**
     * Calculate match percentage based on requirements
     */
    private function calculateMatchPercentage($candidateData, $jobPosition, $requiredSkills, $experienceLevel)
    {
        $score = 0;
        $weights = [
            'skills' => 40,
            'experience' => 30,
            'education' => 15,
            'contact' => 15
        ];
        
        // Skills matching (40%)
        if (!empty($requiredSkills)) {
            $requiredSkillsArray = array_map('trim', explode(',', $requiredSkills));
            $candidateSkills = is_array($candidateData['skills']) ? $candidateData['skills'] : [];
            
            $matchedSkills = 0;
            foreach ($requiredSkillsArray as $skill) {
                foreach ($candidateSkills as $candSkill) {
                    if (stripos($candSkill, $skill) !== false || stripos($skill, $candSkill) !== false) {
                        $matchedSkills++;
                        break;
                    }
                }
            }
            
            if (count($requiredSkillsArray) > 0) {
                $score += ($matchedSkills / count($requiredSkillsArray)) * $weights['skills'];
            } else {
                $score += (count($candidateSkills) > 0 ? 30 : 0);
            }
        } else {
            // No required skills, give points for having any skills
            $candidateSkills = is_array($candidateData['skills']) ? $candidateData['skills'] : [];
            $score += min(count($candidateSkills) * 5, $weights['skills']);
        }
        
        // Experience matching (30%)
        $years = $candidateData['experienceYears'] ?? 0;
        switch ($experienceLevel) {
            case 'entry':
                $score += ($years <= 2) ? $weights['experience'] : max(0, $weights['experience'] - ($years - 2) * 5);
                break;
            case 'mid':
                if ($years >= 2 && $years <= 5) {
                    $score += $weights['experience'];
                } else {
                    $score += max(0, $weights['experience'] - abs($years - 3.5) * 5);
                }
                break;
            case 'senior':
                $score += ($years >= 5) ? $weights['experience'] : max(0, $years * 5);
                break;
            default: // 'any'
                $score += min($years * 3, $weights['experience']);
        }
        
        // Education (15%)
        if (!empty($candidateData['education'])) {
            $score += $weights['education'];
        }
        
        // Contact info completeness (15%)
        $contactScore = 0;
        if (!empty($candidateData['email'])) $contactScore += 5;
        if (!empty($candidateData['phone'])) $contactScore += 5;
        if (!empty($candidateData['name']) && $candidateData['name'] !== 'Unknown Candidate') $contactScore += 5;
        $score += min($contactScore, $weights['contact']);
        
        return min(100, max(0, round($score)));
    }
}
