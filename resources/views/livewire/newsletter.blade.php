<div>
    <form wire:submit.prevent="subscribe">
        <input type="email" name="email" wire:model.debounce.500ms="email" /><input type="submit" value="Subscribe" />
        @if ($message)
            <p class="{{ $error ? 'text-danger' : 'text-success' }} helper-message">{{$message}}</p>
        @endif

        @error('email') <p class="text-danger helper-message">{{ $message }}</p> @enderror
    </form>
</div>
