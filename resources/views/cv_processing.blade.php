@extends('layouts.app')

@php
    function runTest($testName, $testFunction, &$testResults) {
        $testResults['total']++;
        echo "<div class='test-item'>";
        echo "<strong>$testName</strong><br>";
        
        try {
            $result = $testFunction();
            if ($result === true) {
                echo "<span class='status passed'>✅ PASSED</span>";
                $testResults['passed']++;
                echo "<div class='test-item passed'>";
            } elseif ($result === false) {
                echo "<span class='status failed'>❌ FAILED</span>";
                $testResults['failed']++;
                echo "<div class='test-item failed'>";
            } else {
                echo "<span class='status warning'>⚠️ WARNING</span>";
                $testResults['warnings']++;
                echo "<div class='test-item warning'>";
            }
            echo "</div>";
        } catch (Exception $e) {
            echo "<span class='status failed'>❌ ERROR</span>";
            $testResults['failed']++;
            echo "<div class='test-item failed'>Error: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
    }

    $testResults = [
        'total' => 0,
        'passed' => 0,
        'failed' => 0,
        'warnings' => 0
    ];
@endphp

@section('content')
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Processing Testing Checklist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/CvChecker.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
         <!-- Left Navigation -->
         <div class="left-nav">
            <div class="logo">
                <span class="candi">Candi</span><span class="hire">Hire</span>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Overview</div>
                <div class="nav-item" onclick="window.location.href='{{ url('company/dashboard') }}'">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </div>
                <!-- Other nav items omitted for brevity in this specific tool usage but key ones present -->
                <div class="nav-item" onclick="window.location.href='{{ url('company/applications') }}'">
                    <i class="fas fa-file-alt"></i>
                    <span>Applications</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Testing Tools</div>
                <div class="nav-item active">
                    <i class="fas fa-tasks"></i>
                    <span>System Check</span>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="checklist-container">
                <div class="checklist-header">
                    <h1>🔍 CV Processing Project Testing Checklist</h1>
                    <p>Comprehensive testing suite for CV processing functionality</p>
                </div>

                <!-- Test 1: System Requirements -->
                <div class="test-section">
                    <h3>🔧 System Requirements & Environment</h3>
                    @php
                        runTest("PHP Version Check", function() {
                            $version = PHP_VERSION;
                            echo "PHP Version: $version<br>";
                            return version_compare($version, '7.4.0', '>=');
                        }, $testResults);

                        runTest("Required PHP Extensions", function() {
                            $required = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'tokenizer', 'xml'];
                            $missing = [];
                            foreach ($required as $ext) {
                                if (!extension_loaded($ext)) {
                                    $missing[] = $ext;
                                }
                            }
                            if (empty($missing)) {
                                echo "All required extensions loaded: " . implode(', ', $required);
                                return true;
                            } else {
                                echo "Missing extensions: " . implode(', ', $missing);
                                return false;
                            }
                        }, $testResults);
                    @endphp
                </div>

                <!-- Test 2: Database Connection -->
                <div class="test-section">
                     <h3>🗄️ Database Connection & Schema</h3>
                     @php
                        runTest("Database Connection", function() {
                           try {
                               // Use Laravel's DB facade
                               \DB::connection()->getPdo();
                               echo "Database connection successful<br>";
                               return true;
                           } catch (\Exception $e) {
                               echo "Database connection failed: " . $e->getMessage();
                               return false;
                           }
                        }, $testResults);
                     @endphp
                </div>

                <!-- Test 3: PDF Processing Functions -->
                <div class="test-section">
                    <h3>📄 PDF Processing Functions</h3>
                     @php
                        runTest("PDF Text Extraction Capability", function() {
                             // Check for known PDF tools usually available or libraries
                             $hasVendor = file_exists(base_path('vendor/autoload.php'));
                             $hasSpatiePdf = class_exists('Spatie\PdfToText\Pdf');
                             
                             if ($hasVendor) {
                                 echo "Composer vendor directory found.<br>";
                                 if($hasSpatiePdf) {
                                     echo "Spatie PDF to Text library detected.<br>";
                                     return true;
                                 }
                                 echo "Checking for native shell commands...<br>";
                                 $pdftotext = shell_exec('which pdftotext 2>/dev/null');
                                 if ($pdftotext) {
                                     echo "Native pdftotext found at $pdftotext.<br>";
                                     return true;
                                 }
                                 echo "Note: No specific PDF library or tool detected, but PHP can attempt raw reads.<br>";
                                 return null; // Warning
                             }
                             return false;
                        }, $testResults);
                    @endphp
                </div>

                <!-- Summary Section -->
                @php
                    $successRate = $testResults['total'] > 0 ? round(($testResults['passed'] / $testResults['total']) * 100, 1) : 0;
                @endphp
                
                <div class="summary">
                    <h3>📊 Test Summary</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $successRate }}%"></div>
                    </div>
                    <p><strong>Total Tests:</strong> {{ $testResults['total'] }}</p>
                    <p><strong>Passed:</strong> <span style="color: #4CAF50; font-weight: bold;">{{ $testResults['passed'] }}</span></p>
                    <p><strong>Failed:</strong> <span style="color: #f44336; font-weight: bold;">{{ $testResults['failed'] }}</span></p>
                    <p><strong>Warnings:</strong> <span style="color: #ff9800; font-weight: bold;">{{ $testResults['warnings'] }}</span></p>
                    <p><strong>Success Rate:</strong> <span style="font-weight: bold; font-size: 1.2em;">{{ $successRate }}%</span></p>
                </div>

                <!-- Manual Checklist -->
                <div class="test-section">
                    <h3>🧪 Manual Testing Checklist</h3>
                    <div class="code-block">
                        1. CV Upload Process<br>
                           • Upload valid PDF files<br>
                           • Try uploading non-PDF files (should be rejected)<br>
                           • Test with large files<br><br>
                        
                        2. Job Requirements Setting<br>
                           • Set different job positions<br>
                           • Select different experience levels<br><br>
                        
                        3. CV Processing<br>
                           • Process uploaded CVs<br>
                           • Verify match accuracy<br>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
@endsection
