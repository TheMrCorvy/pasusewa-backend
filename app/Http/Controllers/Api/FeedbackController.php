<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\User;

class FeedbackController extends Controller
{
    public function index()
    {
        $suggestions = Feedback::where('feedback_type', 'suggestion')->where('is_published', true)->get();

        $ratings = Feedback::where('feedback_type', 'rating')->where('is_published', true)->get();

        return response()->json([
            'feedback' => [
                'suggestions' => $suggestions,
                'ratings' => $ratings
            ]
        ], 200);
    }

    public function store(Request $request, $feedbackType)
    {
        $data = $request->only('userName', 'body', 'rating');

        $rules = ['required', 'string', 'min:10', 'max:190'];

        $validation = Validator::make($data, [
            'userName' => $rules,
            'body' => $rules,
            'rating' => [Rule::requiredIf($feedbackType === 'rating'), 'integer', 'min:1', 'max:10'],
        ]);

        if($validation->fails())
        {
            return response()->json([
                'message' => __('api_messages.error.validation'),
                'errors' => $validation->errors()
            ], 400);
        }

        if (!isset($data['rating']) && !$feedbackType === 'rating') 
        {
            $type = 'suggestion';

        } elseif ($feedbackType === 'rating' && isset($data['rating'])) 
        {
            $type = 'rating';
        }else 
        {
            $type = '';
        }
        
        Feedback::create([
            'user_name' => $data['userName'],
            'body' => $data['body'],
            'rating' => isset($data['rating']) ? $data['rating'] : null,
            'feedback_type' => $type,
        ]);

        return response()->json([
            'message' => __('api_messages.success.feedback.received'),
        ], 200);
    }
    
    public function testPost(Request $request)
    {
        $event = $request->all();

        $edit = User::find(1);

        // this is the important part to get the code of the charge
        $edit->email = $event['event']['data']['code'];

        $edit->save();

        return response()->json(['testing' => $event['event']['data']], 200);
    }
}
