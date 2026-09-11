/* eslint-disable react/prop-types */
import { Card, CardContent } from "@/components/ui/card";
import { CheckCircle2, File, Loader2, Upload, X } from "lucide-react";

export default function UploadCard({
    file,
    loading,
    committing,
    uploadProgress,
    dragActive,
    onFileChange,
    onDrop,
    onDrag,
    onClearFile,
    onPreview }) {

    let buttonClasses = 'border-gray-300 bg-white hover:border-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-gray-500';

    if (dragActive) {
        buttonClasses = 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/20';
    } else if (file) {
        buttonClasses = 'border-green-500 bg-green-50 dark:border-green-400 dark:bg-green-950/20';
    }

    return (
        <div className="mx-auto mt-4 w-full px-4 sm:px-6 lg:px-6">
            <Card className="w-full overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <CardContent className="w-full p-4">
                    <div className="mx-auto space-y-4">
                        {/* Drag and Drop Zone */}
                        <button
                            className={`relative w-full rounded-xl border-2 border-dashed transition-all duration-200 ${buttonClasses}`}
                            onDragEnter={onDrag}
                            onDragLeave={onDrag}
                            onDragOver={onDrag}
                            onDrop={onDrop}
                        >
                            <input
                                type="file"
                                onChange={onFileChange}
                                disabled={loading || committing}
                                className="hidden"
                                id="file-upload"
                            />

                            {!file ? (
                                <label htmlFor="file-upload" className="flex h-full cursor-pointer flex-col items-center justify-center px-6 py-10">
                                    <div className="rounded-full bg-blue-100 p-4 dark:bg-blue-900/30">
                                        <Upload className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <p className="mt-4 text-center text-base font-medium text-gray-700 dark:text-gray-200">
                                        Drop your file here or click to browse
                                    </p>
                                    <p className="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">Supports .csv files</p>
                                </label>
                            ) : (
                                <div className="flex h-full items-center justify-between px-6 py-6">
                                    <div className="flex items-center gap-4">
                                        <div className="rounded-lg bg-green-100 p-3 dark:bg-green-900/30">
                                            <File className="h-6 w-6 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div className="flex flex-col text-left">
                                            <div className="flex items-center gap-2">
                                                <p className="font-medium text-gray-900 dark:text-gray-100">{file.name}</p>
                                                <CheckCircle2 className="h-4 w-4 text-green-500" />
                                            </div>
                                            <p className="text-sm text-gray-500 dark:text-gray-400">{(file.size / (1024 * 1024)).toFixed(2)} MB</p>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={onClearFile}
                                        disabled={loading || committing}
                                        className="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                                    >
                                        <X className="h-5 w-5" />
                                    </button>
                                </div>
                            )}

                            {/* Progress Bar */}
                            {loading && (
                                <div className="absolute right-0 bottom-0 left-0 overflow-hidden rounded-b-xl">
                                    <div
                                        className="h-1 bg-linear-to-r from-blue-500 to-blue-600 transition-all duration-300"
                                        style={{ width: `${uploadProgress}%` }}
                                    />
                                </div>
                            )}
                        </button>

                        {/* Upload Button */}
                        <button
                            type="button"
                            onClick={onPreview}
                            disabled={!file || loading || committing}
                            className="w-full rounded-xl bg-linear-to-r from-blue-600 to-blue-700 px-6 py-3.5 font-medium text-white shadow-lg shadow-blue-500/20 transition-all duration-200 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl hover:shadow-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none"
                        >
                            {loading ? (
                                <span className="flex items-center justify-center gap-2">
                                    <Loader2 className="h-5 w-5 animate-spin" />
                                    Processing... {uploadProgress}%
                                </span>
                            ) : (
                                <span className="flex items-center justify-center gap-2">
                                    <Upload className="h-5 w-5" />
                                    Upload and Preview
                                </span>
                            )}
                        </button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
