<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Filters\ThreadFilter;
use App\Rules\SpamFree;
use App\Thread;
use App\Trending;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Channel $channel
     * @param ThreadFilter $filters
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function index(Channel $channel, ThreadFilter $filters, Trending $trending)
    {
        $threads = $this->getThreads($channel, $filters);

        if (\request()->wantsJson()) {
            return $threads;
        }

        return view('threads.index', [
            'threads' => $threads,
            'trending' => $trending->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('threads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'title' => ['required', 'string', new SpamFree],
            'body' => ['required', 'string', new SpamFree],
            'channel_id' => 'required|integer|exists:channels,id'
        ]);

        $thread = Thread::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'channel_id' => $data['channel_id'],
            'user_id' => \Auth::id()
        ]);

        $request->session()->put('key', 'value');

        return redirect($thread->path())
            ->with('flash', 'Your thread has been published!');
    }

    /**
     * Display the specified resource.
     *
     * @param Thread $thread
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function show($channel, Thread $thread, Trending $trending)
    {
        if (\Auth::check()) {
            \Auth::user()->seen($thread);
        }

        $trending->push($thread);

        return view('threads/show', compact('thread'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $channel
     * @param Thread $thread
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy($channel, Thread $thread)
    {
        $this->authorize('delete', $thread);

        $thread->delete();

        return redirect()->route('threads.index');
    }

    /**
     * @param Channel $channel
     * @param ThreadFilter $filters
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected function getThreads(Channel $channel, ThreadFilter $filters)
    {
        $threads = Thread::query()
            ->latest()
            ->filter($filters);

        if ($channel->exists) {
            $threads = $channel->threads();
        }

        return $threads->paginate(8);
    }
}
