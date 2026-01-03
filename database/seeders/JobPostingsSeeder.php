<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Support\Facades\DB;

class JobPostingsSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test company if none exists
        $company = Company::first();
        
        if (!$company) {
            $company = Company::create([
                'CompanyName' => 'Tech Solutions Inc.',
                'Industry' => 'Technology',
                'CompanySize' => '51-200',
                'Email' => 'hr@techsolutions.com',
                'PhoneNumber' => '+880 1234567890',
                'CompanyDescription' => 'Leading technology company specializing in software development and IT solutions.',
                'Password' => bcrypt('password123'),
                'IsActive' => 1,
            ]);
        }

        // Insert job postings using raw DB to avoid model casting issues
        $jobs = [
            [
                'CompanyID' => $company->CompanyID,
                'JobTitle' => 'Senior Software Engineer',
                'JobDescription' => 'We are looking for an experienced Senior Software Engineer to join our team. You will be responsible for designing, developing, and maintaining high-quality software solutions.',
                'Requirements' => '5+ years of experience in software development, Strong proficiency in PHP, Laravel, JavaScript, Experience with MySQL and REST APIs',
                'Skills' => 'PHP, Laravel, JavaScript, React, MySQL, REST API, Git',
                'Location' => 'Dhaka, Bangladesh',
                'JobType' => 'Full-time',
                'SalaryMin' => 80000,
                'SalaryMax' => 120000,
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CompanyID' => $company->CompanyID,
                'JobTitle' => 'Frontend Developer',
                'JobDescription' => 'Join our frontend team to build amazing user interfaces. We are looking for a talented developer passionate about creating great user experiences.',
                'Requirements' => '2+ years of experience in frontend development, Strong knowledge of React, Vue, or Angular, Experience with CSS frameworks',
                'Skills' => 'React, Vue.js, HTML5, CSS3, JavaScript, TypeScript, Tailwind CSS',
                'Location' => 'Dhaka, Bangladesh',
                'JobType' => 'Full-time',
                'SalaryMin' => 50000,
                'SalaryMax' => 80000,
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CompanyID' => $company->CompanyID,
                'JobTitle' => 'Junior Web Developer',
                'JobDescription' => 'Great opportunity for fresh graduates! We are looking for enthusiastic junior developers to join our growing team and learn from experienced mentors.',
                'Requirements' => 'Recent graduate in Computer Science or related field, Basic knowledge of HTML, CSS, JavaScript, Willingness to learn',
                'Skills' => 'HTML, CSS, JavaScript, PHP, MySQL',
                'Location' => 'Chittagong, Bangladesh',
                'JobType' => 'Full-time',
                'SalaryMin' => 25000,
                'SalaryMax' => 40000,
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CompanyID' => $company->CompanyID,
                'JobTitle' => 'DevOps Engineer',
                'JobDescription' => 'We need a DevOps Engineer to manage our cloud infrastructure and CI/CD pipelines. Experience with AWS and Docker is essential.',
                'Requirements' => '3+ years DevOps experience, Strong knowledge of AWS services, Experience with Docker and Kubernetes',
                'Skills' => 'AWS, Docker, Kubernetes, CI/CD, Linux, Terraform, Jenkins',
                'Location' => 'Remote',
                'JobType' => 'Full-time',
                'SalaryMin' => 70000,
                'SalaryMax' => 100000,
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'CompanyID' => $company->CompanyID,
                'JobTitle' => 'UI/UX Designer',
                'JobDescription' => 'Creative UI/UX Designer needed to design beautiful and intuitive user interfaces for our web and mobile applications.',
                'Requirements' => '2+ years of UI/UX design experience, Proficiency in Figma or Adobe XD, Strong portfolio showcasing design work',
                'Skills' => 'Figma, Adobe XD, Photoshop, Illustrator, Prototyping, User Research',
                'Location' => 'Dhaka, Bangladesh',
                'JobType' => 'Full-time',
                'SalaryMin' => 45000,
                'SalaryMax' => 70000,
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($jobs as $job) {
            DB::table('job_postings')->insert($job);
        }
    }
}
