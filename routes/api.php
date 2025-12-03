Route::post('/slack/test', function (Request $request) {
    \Log::info('Slack command received:', $request->all());
    
    return response()->json([
        'text' => 'Hello from Laravel! 🎉'
    ]);
});