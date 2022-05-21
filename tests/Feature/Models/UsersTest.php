<?php

namespace Tests\Feature\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Notifications\SubscribedToCourse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UsersTest extends TestCase
{
    // https://github.com/fzaninotto/Faker
    // It will migrate the database run the tests and then refresh te database.
    use DatabaseMigrations;


    public function test_it_subscribes_to_a_course_and_notification()
    {
        $this->withoutExceptionHandling();
        Notification::fake();
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $this->assertFalse($user->isSubscribedToCourse($course));
        $response = $this->actingAs($user)
            ->post(route('courses.subscribe', $course));

        $response->assertRedirect(route('courses.show', $course));

        $user->refresh();
        $this->assertTrue($user->isSubscribedToCourse($course));

        Notification::assertSentTo($user, SubscribedToCourse::class, function($notification) use ($course){
            return $notification->course->id == $course->id;
        });
    }

    public function test_it_checks_if_a_user_is_subscribed_to_a_course(){
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $this->assertFalse($user->isSubscribedToCourse($course));

        $user->subscribeToCourse($course);
        $user->refresh();
        $this->assertTrue($user->isSubscribedToCourse($course));
    }

    public function test_it_does_not_allow_a_user_to_subscribe_again()
    {
        Notification::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create();
        $user->subscribeToCourse($course);

        $user->refresh();

        $response = $this->actingAs($user)
            ->post(route('courses.subscribe', $course));
        $user->refresh();

        $this->assertEquals(1, $user->courses()->count());
    }
}
