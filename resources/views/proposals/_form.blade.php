@php
    $proposal = $proposal ?? null;
    $val = fn (string $key, mixed $default = '') => old($key, $default);
    $list = function (string $key, ?array $items, string $glue = ', ') {
        $value = old($key, $items ?? []);

        return is_array($value) ? implode($glue, $value) : $value;
    };
@endphp

@csrf

<div class="field {{ $errors->has('title') ? 'has-err' : '' }}">
    <label for="in-title">Title</label>
    <input id="in-title" name="title" value="{{ $val('title', $proposal?->title) }}" placeholder="Effects of sleep on memory consolidation" required maxlength="255">
    @error('title')<div class="err">{{ $message }}</div>@enderror
</div>

<div class="field {{ $errors->has('abstract') ? 'has-err' : '' }}">
    <label for="in-abstract">Abstract <span class="hint">at least 50 characters</span></label>
    <textarea id="in-abstract" name="abstract" required>{{ $val('abstract', $proposal?->abstract) }}</textarea>
    @error('abstract')<div class="err">{{ $message }}</div>@enderror
</div>

<div class="field {{ $errors->has('proponents') || $errors->has('proponents.*') ? 'has-err' : '' }}">
    <label for="in-proponents">Proponents <span class="hint">separate with commas</span></label>
    <input id="in-proponents" name="proponents" value="{{ $list('proponents', $proposal?->proponents) }}" placeholder="Ada Lovelace, Alan Turing" required>
    @error('proponents')<div class="err">{{ $message }}</div>@enderror
    @error('proponents.*')<div class="err">{{ $message }}</div>@enderror
</div>

<div class="field {{ $errors->has('objectives') || $errors->has('objectives.*') ? 'has-err' : '' }}">
    <label for="in-objectives">Objectives <span class="hint">one per line</span></label>
    <textarea id="in-objectives" name="objectives" required>{{ $list('objectives', $proposal?->objectives, "\n") }}</textarea>
    @error('objectives')<div class="err">{{ $message }}</div>@enderror
    @error('objectives.*')<div class="err">{{ $message }}</div>@enderror
</div>

<div class="field-row">
    <div class="field {{ $errors->has('research_area') ? 'has-err' : '' }}">
        <label for="in-research-area">Research area</label>
        <select id="in-research-area" name="research_area" required>
            <option value="">Select an area</option>
            @foreach (\App\Enums\ResearchArea::cases() as $area)
                <option value="{{ $area->value }}" @selected($val('research_area', $proposal?->research_area->value) === $area->value)>{{ $area->label() }}</option>
            @endforeach
        </select>
        @error('research_area')<div class="err">{{ $message }}</div>@enderror
    </div>
</div>

<div class="field-row">
    <div class="field {{ $errors->has('start_date') ? 'has-err' : '' }}">
        <label for="in-start-date">Start date</label>
        <input id="in-start-date" name="start_date" type="date" value="{{ $val('start_date', $proposal?->start_date->format('Y-m-d')) }}" required>
        @error('start_date')<div class="err">{{ $message }}</div>@enderror
    </div>
    <div class="field {{ $errors->has('end_date') ? 'has-err' : '' }}">
        <label for="in-end-date">End date</label>
        <input id="in-end-date" name="end_date" type="date" value="{{ $val('end_date', $proposal?->end_date->format('Y-m-d')) }}" required>
        @error('end_date')<div class="err">{{ $message }}</div>@enderror
    </div>
</div>

<div class="field {{ $errors->has('budget') ? 'has-err' : '' }}">
    <label for="in-budget">Budget <span class="hint">PHP</span></label>
    <input id="in-budget" name="budget" type="number" step="0.01" min="0" value="{{ $val('budget', $proposal?->budget) }}" placeholder="250000.00" required>
    @error('budget')<div class="err">{{ $message }}</div>@enderror
</div>

<div class="field {{ $errors->has('keywords') || $errors->has('keywords.*') ? 'has-err' : '' }}">
    <label for="in-keywords">Keywords <span class="hint">optional, separate with commas</span></label>
    <input id="in-keywords" name="keywords" value="{{ $list('keywords', $proposal?->keywords) }}" placeholder="memory, sleep, neuroscience">
    @error('keywords')<div class="err">{{ $message }}</div>@enderror
    @error('keywords.*')<div class="err">{{ $message }}</div>@enderror
</div>

<div class="form-foot">
    <a class="btn-ghost" href="{{ $cancelUrl }}">Cancel</a>
    <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
</div>
