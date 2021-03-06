<?php


namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BestReplyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_thread_creator_may_mark_any_reply_as_the_best_reply()
    {
        $this->signIn();

        $thread = factory('App\Thread')->create([
            'user_id' => auth()->id()
        ]);

        $replies = factory('App\Reply', 2)->create(['thread_id' => $thread->id]);

        $this->assertFalse($replies[1]->isBest());

        $this->withoutExceptionHandling()
            ->postJson(route('best-replies.store', $replies[1]->id));

        $this->assertTrue($replies[1]->fresh()->isBest());
    }

    /** @test */
    public function only_the_thread_creator_may_mark_a_reply_as_best()
    {
        $this->signIn();

        $thread = factory('App\Thread')->create([
            'user_id' => auth()->id()
        ]);

        $replies = factory('App\Reply', 2)->create(['thread_id' => $thread->id]);

        $this->signIn(
            factory('App\User')->create()
        );

        $this->postJson(route('best-replies.store', $replies[1]->id))
            ->assertStatus(403);

        $this->assertFalse($replies[1]->fresh()->isBest());
    }
    
    /** @test */
    public function if_a_best_reply_deleted_then_the_thread_is_properly_updated_to_reflect_that()
    {
        $this->signIn();

        $thread = factory('App\Thread')->create();

        $reply = factory('App\Reply')->create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id()
        ]);

        $thread->markBestReply($reply);

        $this->assertEquals($thread->fresh()->best_reply_id, $reply->id);

        $this->deleteJson(route('replies.destroy', $reply));

        $this->assertNull($thread->fresh()->best_reply_id);
    }
}
