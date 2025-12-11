<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidateProfileController extends Controller
{
    /**
     * Get candidate profile data (GET request)
     */
    public function show(Request $request)
    {
        $candidateId = $request->query('candidateId');
        
        if (!$candidateId) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate ID required'
            ]);
        }

        try {
            $candidate = DB::table('candidates')
                ->where('CandidateID', $candidateId)
                ->where('IsActive', 1)
                ->select(
                    'CandidateID', 'FullName', 'Email', 'PhoneNumber', 
                    'WorkType', 'Skills', 'ProfilePicture', 'Location', 
                    'Summary', 'LinkedIn', 'GitHub', 'Portfolio',
                    'YearsOfExperience', 'created_at', 'updated_at'
                )
                ->first();

            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ]);
            }

            return response()->json([
                'success' => true,
                'candidate' => $candidate
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update candidate profile (POST request)
     */
    public function update(Request $request)
    {
        $candidateId = $request->input('candidateId');
        
        if (!$candidateId) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate ID required'
            ]);
        }

        try {
            // Validate candidate exists and is active
            $exists = DB::table('candidates')
                ->where('CandidateID', $candidateId)
                ->where('IsActive', 1)
                ->exists();

            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ]);
            }

            $updateData = [];

            // Handle file upload for profile picture
            if ($request->hasFile('profilePicture')) {
                $file = $request->file('profilePicture');
                
                // Validate file
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $extension = strtolower($file->getClientOriginalExtension());
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'
                    ]);
                }

                // Check file size (max 5MB)
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Profile picture file is too large. Maximum size is 5MB.'
                    ]);
                }

                // Store file
                $fileName = 'candidate_' . $candidateId . '_' . time() . '.' . $extension;
                $file->move(public_path('uploads/profiles'), $fileName);
                $updateData['ProfilePicture'] = 'uploads/profiles/' . $fileName;
            }

            // Handle text fields
            $fieldMapping = [
                'fullName' => 'FullName',
                'phoneNumber' => 'PhoneNumber',
                'workType' => 'WorkType',
                'skills' => 'Skills',
                'location' => 'Location',
                'summary' => 'Summary',
                'linkedin' => 'LinkedIn',
                'github' => 'GitHub',
                'portfolio' => 'Portfolio',
                'yearsOfExperience' => 'YearsOfExperience'
            ];

            foreach ($fieldMapping as $inputField => $dbField) {
                if ($request->has($inputField)) {
                    $value = trim($request->input($inputField));
                    
                    // Special validation for years of experience
                    if ($inputField === 'yearsOfExperience') {
                        $value = intval($value);
                        if ($value < 0 || $value > 50) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Years of experience must be between 0 and 50'
                            ]);
                        }
                    }
                    
                    $updateData[$dbField] = $value;
                }
            }

            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fields to update'
                ]);
            }

            // Add updated_at field
            $updateData['updated_at'] = now();

            // Update the database
            DB::table('candidates')
                ->where('CandidateID', $candidateId)
                ->update($updateData);

            // Get updated profile data
            $updatedProfile = DB::table('candidates')
                ->where('CandidateID', $candidateId)
                ->select('FullName', 'ProfilePicture')
                ->first();

            // Update session if we have the full name
            if ($updatedProfile && $updatedProfile->FullName) {
                session(['candidate_name' => $updatedProfile->FullName]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'profilePicture' => $updatedProfile->ProfilePicture ?? null,
                'fullName' => $updatedProfile->FullName ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
