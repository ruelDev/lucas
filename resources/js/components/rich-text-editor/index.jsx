import Highlight from '@tiptap/extension-highlight'
import Placeholder from '@tiptap/extension-placeholder'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import { EditorContent, useEditor } from '@tiptap/react'
import MenuBar from './menu-bar'

const RichTextEditor = ({ content, onChange, placeholder }) => {
    const editor = useEditor({
        extensions: [
            Highlight,
            Placeholder.configure({
                placeholder: placeholder,
            }),
            StarterKit.configure({
                bulletList: {
                    HTMLAttributes: {
                        class: "list-disc ml-3"
                    }
                },
                orderedList: {
                    HTMLAttributes: {
                        class: "list-decimal ml-3"
                    }
                },
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        content: content,
        editorProps: {
            attributes: {
                class: 'min-h-[350px] border rounded-md bg-slate-50 py-2 px-3'
            }
        },
        onUpdate: ({ editor }) => {
            onChange(editor.getHTML());
        }
    })

    return (
        <div>
            <MenuBar editor={editor} />
            <EditorContent editor={editor} />
        </div>
    )
}

export default RichTextEditor;
