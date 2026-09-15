@extends('layouts.library')

@section('title', __('labels.script_convertor'))

@push('styles')
<style>
    .converter-page {
        padding: 2rem 0 4rem;
    }

    .converter-title {
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--tblr-body-color, #1e293b);
    }

    .converter-layout {
        display: flex;
        gap: 1rem;
        align-items: stretch;
        min-height: 400px;
    }

    .converter-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .converter-panel label {
        font-weight: 500;
        margin-bottom: .5rem;
        font-size: .875rem;
        color: var(--tblr-body-color, #475569);
    }

    .converter-panel select {
        margin-bottom: .75rem;
        padding: .5rem .75rem;
        border: 1px solid var(--tblr-border-color, #d1d5db);
        border-radius: .375rem;
        font-size: .875rem;
        background: var(--tblr-bg-surface, #fff);
        color: var(--tblr-body-color, #1e293b);
    }

    .converter-panel textarea {
        flex: 1;
        min-height: 300px;
        padding: .75rem;
        border: 1px solid var(--tblr-border-color, #d1d5db);
        border-radius: .375rem;
        font-family: 'Noto Sans', 'Noto Sans CJK SC', 'Noto Sans Myanmar', sans-serif;
        font-size: 1rem;
        line-height: 1.6;
        resize: vertical;
        background: var(--tblr-bg-surface, #fff);
        color: var(--tblr-body-color, #1e293b);
    }

    .converter-panel textarea:focus,
    .converter-panel select:focus {
        outline: none;
        border-color: var(--tblr-primary, #0054a6);
        box-shadow: 0 0 0 .25rem rgba(0, 84, 166, .1);
    }

    .converter-middle {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        min-width: 120px;
        padding: 1rem 0;
    }

    .converter-middle label {
        font-size: .8rem;
        font-weight: 500;
        text-align: center;
        color: var(--tblr-body-color, #475569);
    }

    .converter-middle select {
        padding: .375rem .5rem;
        border: 1px solid var(--tblr-border-color, #d1d5db);
        border-radius: .375rem;
        font-size: .875rem;
        background: var(--tblr-bg-surface, #fff);
    }

    .btn-convert {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .5rem 1.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        border: none;
        border-radius: .375rem;
        background: var(--tblr-primary, #0054a6);
        color: #fff;
        cursor: pointer;
        transition: background .15s;
    }

    .btn-convert:hover {
        background: var(--tblr-primary-darken, #004080);
    }

    @media (max-width: 768px) {
        .converter-layout {
            flex-direction: column;
        }

        .converter-middle {
            flex-direction: row;
            justify-content: center;
            min-width: unset;
            padding: .5rem 0;
        }

        .converter-panel textarea {
            min-height: 200px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-body">
    <div class="container-xl converter-page">
        <h1 class="converter-title">{{ __('labels.script_convertor') }}</h1>

        <div class="converter-layout">
            {{-- Input --}}
            <div class="converter-panel">
                <label for="inputScript">{{ __('labels.input_script') }}</label>
                <select id="inputScript">
                    <option value="roman">{{ __('labels.script_roman') }}</option>
                    <option value="sangayana">{{ __('labels.script_sangayana') }}</option>
                    <option value="sinhala">{{ __('labels.script_sinhala') }}</option>
                    <option value="myanmar">{{ __('labels.script_myanmar') }}</option>
                    <option value="tai_tham">{{ __('labels.script_tai_tham') }}</option>
                    <option value="thai">{{ __('labels.script_thai') }}</option>
                    <option value="tai_old">{{ __('labels.script_tai_old') }}</option>
                </select>
                <textarea id="txtInput" placeholder="{{ __('labels.paste_here') }}"></textarea>
            </div>

            {{-- Middle --}}
            <div class="converter-middle">
                <div>
                    <label for="niggahitaSelect">{{ __('labels.niggahita_label') }}</label>
                    <select id="niggahitaSelect">
                        <option value="ṃ,Ṃ">ṃ</option>
                        <option value="ṁ,Ṁ">ṁ</option>
                        <option value="ŋ,Ŋ">ŋ</option>
                    </select>
                </div>
                <button type="button" class="btn-convert" onclick="convert()">→</button>
            </div>

            {{-- Output --}}
            <div class="converter-panel">
                <label for="outputScript">{{ __('labels.output_script') }}</label>
                <select id="outputScript">
                    <option value="roman">{{ __('labels.script_roman') }}</option>
                    <option value="sangayana">{{ __('labels.script_sangayana') }}</option>
                    <option value="sinhala1">{{ __('labels.script_sinhala_traditional') }}</option>
                    <option value="sinhala2">{{ __('labels.script_sinhala_modern') }}</option>
                    <option value="chinese_detail">{{ __('labels.script_chinese_detail') }}</option>
                    <option value="chinese_simple">{{ __('labels.script_chinese_simple') }}</option>
                    <option value="chinese_pinyin">{{ __('labels.script_chinese_pinyin') }}</option>
                    <option value="telugu">{{ __('labels.script_telugu') }}</option>
                    <option value="myanmar">{{ __('labels.script_myanmar') }}</option>
                    <option value="tai_tham">{{ __('labels.script_tai_tham') }}</option>
                    <option value="thai">{{ __('labels.script_thai') }}</option>
                </select>
                <textarea id="txtOutput" placeholder="{{ __('labels.converted_text') }}" readonly></textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('charcode/unicode.js') }}"></script>
<script src="{{ asset('charcode/sinhala.js') }}"></script>
<script src="{{ asset('charcode/myanmar.js') }}"></script>
<script src="{{ asset('charcode/tai_tham.js') }}"></script>
<script src="{{ asset('charcode/thai.js') }}"></script>
<script src="{{ asset('charcode/chinese.js') }}"></script>
<script src="{{ asset('charcode/telugu.js') }}"></script>
<script>
(function () {
    function applyMap(text, map) {
        for (let i = 0; i < map.length; i++) {
            text = text.replace(new RegExp(map[i].id, 'g'), map[i].value);
        }
        return text;
    }

    function getNiggahita() {
        const val = document.getElementById('niggahitaSelect').value;
        const parts = val.split(',');
        return { lower: parts[0], upper: parts[1] };
    }

    function sangayanaToUnicode(text) {
        const n = getNiggahita();
        text = text.replace(/ïk/g, n.lower + 'k');
        text = text.replace(/ïg/g, n.lower + 'g');
        text = text.replace(/ü/g, n.lower);
        text = text.replace(/§/g, n.lower);
        text = text.replace(/ṃ/g, n.lower);
        text = text.replace(/ðK/g, n.upper + 'K');
        text = text.replace(/ðG/g, n.upper + 'G');
        text = text.replace(/ý/g, n.upper);
        return applyMap(text, char_sanga_to_unicode);
    }

    function unicodeToSangayana(text) {
        return applyMap(text, char_unicode_to_sanga);
    }

    function normalizeNiggahita(text) {
        const n = getNiggahita();
        text = text.replace(/ṅk/g, n.lower + 'k');
        text = text.replace(/ṅg/g, n.lower + 'g');
        text = text.replace(/ŋk/g, n.lower + 'k');
        text = text.replace(/ŋg/g, n.lower + 'g');
        text = text.replace(/ŋ/g, n.lower);
        text = text.replace(/ṁ/g, n.lower);
        text = text.replace(/ṃ/g, n.lower);
        text = text.replace(/ṃk/g, 'ṅk');
        text = text.replace(/ṁk/g, 'ṅk');
        text = text.replace(/ṃg/g, 'ṅg');
        text = text.replace(/ṁg/g, 'ṅg');
        text = text.replace(/ṄK/g, n.upper + 'K');
        text = text.replace(/ṄG/g, n.upper + 'G');
        text = text.replace(/ŊK/g, n.upper + 'K');
        text = text.replace(/ŊG/g, n.upper + 'G');
        text = text.replace(/Ŋ/g, n.upper);
        text = text.replace(/Ṁ/g, n.upper);
        text = text.replace(/Ṃ/g, n.upper);
        text = text.replace(/ṂK/g, 'ṄG');
        text = text.replace(/ṀK/g, 'ṄG');
        text = text.replace(/ṂG/g, 'ṄG');
        text = text.replace(/ṀG/g, 'ṄG');
        return text;
    }

    function toRoman(text, inputType) {
        switch (inputType) {
            case 'sangayana':
                return sangayanaToUnicode(text);
            case 'sinhala':
                text = applyMap(text, char_si_to_unicode);
                return normalizeNiggahita(text);
            case 'myanmar':
                text = applyMap(text, char_myn_to_roman_1);
                return normalizeNiggahita(text);
            case 'tai_tham':
                text = applyMap(text, char_tai_to_roman);
                return normalizeNiggahita(text);
            case 'thai':
                text = applyMap(text, char_thai_to_roman);
                return normalizeNiggahita(text);
            case 'tai_old':
                text = applyMap(text, char_tai_old_to_r);
                return normalizeNiggahita(text);
            default:
                return normalizeNiggahita(text);
        }
    }

    function fromRoman(text, outputType) {
        text = text.toLowerCase();
        switch (outputType) {
            case 'sangayana':
                return unicodeToSangayana(' ' + text);
            case 'sinhala1':
                return applyMap(text, char_unicode_to_si_c);
            case 'sinhala2':
                return applyMap(text, char_unicode_to_si_n);
            case 'chinese_detail':
                text = applyMap(text, char_chinese_pronounce_1);
                return normalizeNiggahita(text);
            case 'chinese_simple':
                text = applyMap(text, char_chinese_pronounce_2);
                return normalizeNiggahita(text);
            case 'chinese_pinyin':
                text = ' ' + text;
                text = applyMap(text, char_chinese_pinyin);
                text = text.toLowerCase();
                return normalizeNiggahita(text);
            case 'telugu':
                text = applyMap(text, char_unicode_to_telugu);
                return normalizeNiggahita(text);
            case 'myanmar':
                text = applyMap(text, char_roman_to_myn);
                return normalizeNiggahita(text);
            case 'tai_tham':
                text = applyMap(text, char_roman_to_tai);
                return normalizeNiggahita(text);
            case 'thai':
                text = applyMap(text, char_roman_to_thai);
                return normalizeNiggahita(text);
            default:
                return normalizeNiggahita(text);
        }
    }

    window.convert = function () {
        const inputType = document.getElementById('inputScript').value;
        const outputType = document.getElementById('outputScript').value;
        const inputText = document.getElementById('txtInput').value;

        const roman = toRoman(inputText, inputType);
        const result = fromRoman(roman, outputType);

        document.getElementById('txtOutput').value = result;
    };
})();
</script>
@endpush
