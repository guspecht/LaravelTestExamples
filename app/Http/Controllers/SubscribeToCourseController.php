<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Notifications\SubscribedToCourse;

class SubscribeToCourseController extends Controller
{
    public function __invoke(Course $course)
    {
        auth()->user()->subscribeToCourse($course);
        auth()->user()->notify(new SubscribedToCourse($course));
        return redirect(route('courses.show', $course));
    }
}
