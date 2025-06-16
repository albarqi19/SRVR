@php
    // Get the state from the column
    $state = $getState();
    
    // Ensure we have a numeric value
    if (is_null($state) || $state === '' || !is_numeric($state)) {
        $numericState = 0;
    } else {
        $numericState = (float) $state;
    }
    
    // Format the percentage display
    $formattedState = number_format($numericState, 1) . '%';
    
    // Calculate progress width (ensure it's between 0 and 100)
    $progressWidth = min(max(0, $numericState), 100);
@endphp

<div class="px-4 py-3">
    <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700 h-2.5">
        <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $progressWidth }}%"></div>
    </div>
    <div class="text-center text-sm mt-2 font-medium text-gray-700 dark:text-gray-300">
        {{ $formattedState }}
    </div>
</div>
