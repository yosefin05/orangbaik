@props(['name', 'value' => '', 'id' => null])

@php($editorId = $id ?: $name . '-editor')
<div class="rich-text-editor" data-rich-text-editor>
    <div class="rich-text-toolbar" role="toolbar" aria-label="Format teks">
        <button type="button" data-command="bold" title="Tebal"><strong>B</strong></button>
        <button type="button" data-command="italic" title="Miring"><em>I</em></button>
        <button type="button" data-command="underline" title="Garis bawah"><u>U</u></button>
        <button type="button" data-command="insertUnorderedList" title="Daftar">&bull;</button>
        <button type="button" data-command="insertOrderedList" title="Daftar bernomor">1.</button>
        <button type="button" data-command="createLink" title="Tautan">Link</button>
        <button type="button" data-insert-image title="Sisipkan gambar">Gambar</button>
        <input type="file" accept="image/*" data-image-input hidden>
    </div>
    <div id="{{ $editorId }}" class="rich-text-content" contenteditable="true" role="textbox" aria-multiline="true">
        {!! $value !!}</div>
    <textarea name="{{ $name }}" data-rich-text-input hidden>{{ $value }}</textarea>
</div>

@once
    <style>
        .rich-text-editor {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }

        .rich-text-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .rich-text-toolbar button {
            min-width: 32px;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
        }

        .rich-text-toolbar button:hover {
            background: #e2e8f0;
        }

        .rich-text-content {
            min-height: 220px;
            padding: 12px;
            outline: none;
            line-height: 1.65;
        }

        .rich-text-content:focus {
            box-shadow: inset 0 0 0 2px #93c5fd;
        }

        .rich-text-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 12px 0;
        }

        .rich-text-content a {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
    <script src="{{ asset('js/rich-text-editor.js') }}"></script>
@endonce