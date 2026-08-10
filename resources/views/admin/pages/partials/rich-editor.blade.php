@once
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/admin-pages-rich-editor.css') }}">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
window.richEditors = window.richEditors || new Map();

window.createRichEditor = function(textarea) {
    if (!textarea) return Promise.reject(new Error('Editor target missing'));
    if (window.richEditors.has(textarea)) return Promise.resolve(window.richEditors.get(textarea));
    if (typeof Quill === 'undefined') return Promise.reject(new Error('Quill script not loaded'));

    const wrapper = document.createElement('div');
    const compact = textarea.classList.contains('faq-answer');
    wrapper.className = compact ? 'cms-rich-editor cms-rich-editor-compact' : 'cms-rich-editor';
    const editorElement = document.createElement('div');
    wrapper.appendChild(editorElement);
    textarea.classList.add('hidden');
    textarea.insertAdjacentElement('afterend', wrapper);
    editorElement.innerHTML = textarea.value || '';

    const quill = new Quill(editorElement, {
        theme: 'snow',
        modules: {
            toolbar: compact ? [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'clean']
            ] : [
                [{ header: [1, 2, 3, 4, false] }],
                [{ size: ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ script: 'sub' }, { script: 'super' }],
                [{ list: 'ordered' }, { list: 'bullet' }, { list: 'check' }],
                [{ indent: '-1' }, { indent: '+1' }],
                [{ align: [] }, { direction: 'rtl' }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    const editor = {
        sourceElement: textarea,
        instance: quill,
        getData() {
            return quill.root.innerHTML;
        },
        sync() {
            textarea.value = quill.root.innerHTML;
        }
    };

    quill.on('text-change', () => editor.sync());
    editor.sync();
    window.richEditors.set(textarea, editor);
    return Promise.resolve(editor);
};

window.syncRichEditors = function() {
    window.richEditors?.forEach(editor => {
        if (editor && typeof editor.sync === 'function') editor.sync();
    });
};
</script>
@endonce
