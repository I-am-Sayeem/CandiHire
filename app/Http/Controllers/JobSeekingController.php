<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobSeekingController extends Controller
{
    /**
     * Get job seeking posts (GET request)
     */
    public function index(Request $request)
    {
        $candidateId = $request->query('candidateId');
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);
        $myPosts = $request->query('myPosts', false);

        try {
            if ($myPosts && $candidateId) {
                // Get only the candidate's posts (simple query without JOIN)
                $posts = DB::table('job_seeking_posts')
                    ->where('CandidateID', $candidateId)
                    ->select(
                        'PostID',
                        'JobTitle',
                        'CareerGoal',
                        'KeySkills',
                        'Experience',
                        'Education',
                        'SoftSkills',
                        'ValueToEmployer',
                        'ContactInfo',
                        'Status',
                        'CreatedAt',
                        'Views',
                        'Applications'
                    )
                    ->orderBy('CreatedAt', 'desc')
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

                $totalPosts = DB::table('job_seeking_posts')
                    ->where('CandidateID', $candidateId)
                    ->count();

                return response()->json([
                    'success' => true,
                    'posts' => $posts,
                    'total' => $totalPosts,
                    'hasMore' => ($offset + $limit) < $totalPosts
                ]);
            } else {
                // Get all active job seeking posts with candidate info
                $query = DB::table('job_seeking_posts')
                    ->join('candidates', 'job_seeking_posts.CandidateID', '=', 'candidates.CandidateID')
                    ->where('job_seeking_posts.Status', 'active');

                // Search functionality
                $search = $request->query('search');
                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('job_seeking_posts.JobTitle', 'like', "%$search%")
                          ->orWhere('job_seeking_posts.KeySkills', 'like', "%$search%")
                          ->orWhere('job_seeking_posts.CareerGoal', 'like', "%$search%")
                          ->orWhere('candidates.FullName', 'like', "%$search%");
                    });
                }

                $posts = $query->select(
                        'job_seeking_posts.PostID',
                        'job_seeking_posts.CandidateID',
                        'job_seeking_posts.JobTitle',
                        'job_seeking_posts.CareerGoal',
                        'job_seeking_posts.KeySkills',
                        'job_seeking_posts.Experience',
                        'job_seeking_posts.Education',
                        'job_seeking_posts.SoftSkills',
                        'job_seeking_posts.ValueToEmployer',
                        'job_seeking_posts.ContactInfo',
                        'job_seeking_posts.Status',
                        'job_seeking_posts.CreatedAt',
                        'job_seeking_posts.Views',
                        'job_seeking_posts.Applications',
                        'candidates.FullName',
                        'candidates.ProfilePicture'
                    )
                    ->orderBy('job_seeking_posts.CreatedAt', 'desc')
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

                $totalPosts = DB::table('job_seeking_posts')
                    ->where('Status', 'active')
                    ->count();

                return response()->json([
                    'success' => true,
                    'posts' => $posts,
                    'total' => $totalPosts,
                    'hasMore' => ($offset + $limit) < $totalPosts
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle POST requests (create, update, delete)
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $action = $input['action'] ?? '';

        try {
            if ($action === 'delete_post') {
                return $this->deletePost($input);
            } elseif ($action === 'update_post') {
                return $this->updatePost($input);
            } else {
                return $this->createPost($input);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new job seeking post
     */
    private function createPost($input)
    {
        $required = ['candidateId', 'jobTitle', 'careerGoal', 'keySkills', 'education', 'contactInfo'];
        $missing = [];
        foreach ($required as $field) {
            if (empty(trim($input[$field] ?? ''))) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missing)
            ]);
        }

        $postId = DB::table('job_seeking_posts')->insertGetId([
            'CandidateID' => $input['candidateId'],
            'JobTitle' => trim($input['jobTitle']),
            'CareerGoal' => trim($input['careerGoal']),
            'KeySkills' => trim($input['keySkills']),
            'Experience' => trim($input['experience'] ?? ''),
            'Education' => trim($input['education']),
            'SoftSkills' => trim($input['softSkills'] ?? ''),
            'ValueToEmployer' => trim($input['valueToEmployer'] ?? ''),
            'ContactInfo' => trim($input['contactInfo']),
            'Status' => 'active',
            'CreatedAt' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job seeking post created successfully',
            'postId' => $postId
        ]);
    }

    /**
     * Update an existing job seeking post
     */
    private function updatePost($input)
    {
        $postId = $input['postId'] ?? null;
        $candidateId = $input['candidateId'] ?? null;

        if (!$postId || !$candidateId) {
            return response()->json([
                'success' => false,
                'message' => 'Post ID and Candidate ID are required'
            ]);
        }

        // Verify ownership
        $post = DB::table('job_seeking_posts')
            ->where('PostID', $postId)
            ->where('CandidateID', $candidateId)
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or you do not have permission to update it'
            ]);
        }

        DB::table('job_seeking_posts')
            ->where('PostID', $postId)
            ->where('CandidateID', $candidateId)
            ->update([
                'JobTitle' => trim($input['jobTitle']),
                'CareerGoal' => trim($input['careerGoal']),
                'KeySkills' => trim($input['keySkills']),
                'Experience' => trim($input['experience'] ?? ''),
                'Education' => trim($input['education']),
                'SoftSkills' => trim($input['softSkills'] ?? ''),
                'ValueToEmployer' => trim($input['valueToEmployer'] ?? ''),
                'ContactInfo' => trim($input['contactInfo']),
                'UpdatedAt' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully'
        ]);
    }

    /**
     * Delete a job seeking post
     */
    private function deletePost($input)
    {
        $postId = $input['postId'] ?? null;
        $candidateId = $input['candidateId'] ?? null;

        if (!$postId || !$candidateId) {
            return response()->json([
                'success' => false,
                'message' => 'Post ID and Candidate ID are required'
            ]);
        }

        // Verify ownership
        $post = DB::table('job_seeking_posts')
            ->where('PostID', $postId)
            ->where('CandidateID', $candidateId)
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or you do not have permission to delete it'
            ]);
        }

        DB::table('job_seeking_posts')
            ->where('PostID', $postId)
            ->where('CandidateID', $candidateId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }
}
