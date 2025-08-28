import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useCallback, useRef } from 'react';
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Progress } from "@/components/ui/progress";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { 
    Upload, 
    X, 
    File, 
    Image as ImageIcon, 
    AlertCircle,
    CheckCircle,
    Loader2,
    Plus
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface FileWithPreview extends File {
    id: string;
    preview?: string;
    progress?: number;
    error?: string;
    success?: boolean;
    uploading?: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Media Library',
        href: '/admin/media',
    },
    {
        title: 'Upload Media',
        href: '/admin/media/create',
    },
];

const MediaCreate = () => {
    const [files, setFiles] = useState<FileWithPreview[]>([]);
    const [isDragOver, setIsDragOver] = useState(false);
    const [isUploading, setIsUploading] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        files: [] as File[],
        is_public: true,
    });

    const handleDragOver = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(true);
    }, []);

    const handleDragLeave = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);
    }, []);

    const handleDrop = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);
        
        const droppedFiles = Array.from(e.dataTransfer.files);
        addFiles(droppedFiles);
    }, []);

    const handleFileSelect = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            const selectedFiles = Array.from(e.target.files);
            addFiles(selectedFiles);
        }
    }, []);

    const addFiles = (newFiles: File[]) => {
        const processedFiles: FileWithPreview[] = newFiles.map(file => {
            const fileWithId: FileWithPreview = Object.assign(file, {
                id: Math.random().toString(36).substr(2, 9),
                progress: 0,
                uploading: false,
                success: false,
            });

            // Create preview for images
            if (file.type && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    fileWithId.preview = e.target?.result as string;
                    setFiles(prev => [...prev]);
                };
                reader.readAsDataURL(file);
            }

            return fileWithId;
        });

        setFiles(prev => [...prev, ...processedFiles]);
        setData('files', [...data.files, ...newFiles]);
    };

    const removeFile = (fileId: string) => {
        setFiles(prev => {
            const updated = prev.filter(f => f.id !== fileId);
            setData('files', updated);
            return updated;
        });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (files.length === 0) {
            alert('Please select at least one file to upload.');
            return;
        }

        setIsUploading(true);
        
        // Mark all files as uploading
        setFiles(prev => prev.map(file => ({ ...file, uploading: true, progress: 0 })));

        post(route('admin.media.store'), {
            onSuccess: () => {
                setFiles(prev => prev.map(file => ({ 
                    ...file, 
                    uploading: false, 
                    success: true, 
                    progress: 100 
                })));
                setIsUploading(false);
                
                setTimeout(() => {
                    reset();
                    setFiles([]);
                }, 2000);
            },
            onError: () => {
                setFiles(prev => prev.map(file => ({ 
                    ...file, 
                    uploading: false, 
                    error: 'Upload failed',
                    progress: 0
                })));
                setIsUploading(false);
            },
            onProgress: (progress) => {
                // DEBUG: Let's see what Inertia is actually sending
                console.log('Full progress object:', progress);
                console.log('Type of progress:', typeof progress);
                console.log('Progress keys:', Object.keys(progress || {}));
                
                // Try all possible progress formats
                let percentage = 0;
                if (progress?.percentage !== undefined) {
                    percentage = progress.percentage;
                    console.log('Using progress.percentage:', percentage);
                } else if (progress?.loaded && progress?.total) {
                    percentage = (progress.loaded / progress.total) * 100;
                    console.log('Calculated from loaded/total:', percentage);
                } else if (typeof progress === 'number') {
                    percentage = progress;
                    console.log('Progress is number:', percentage);
                } else {
                    console.log('Could not determine progress, defaulting to 0');
                }
                
                const finalPercentage = Math.max(0, Math.min(100, Math.round(percentage)));
                console.log('Final percentage:', finalPercentage);
                
                setFiles(prev => prev.map(file => ({ 
                    ...file, 
                    progress: file.uploading ? finalPercentage : file.progress 
                })));
            }
        });
    };

    const getFileIcon = (file: File) => {
        if (file.type && file.type.startsWith('image/')) {
            return <ImageIcon className="h-8 w-8 text-green-600" />;
        }
        return <File className="h-8 w-8 text-blue-600" />;
    };

    const getFileSize = (bytes: number) => {
        if (!bytes || bytes === 0 || isNaN(bytes)) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Upload Media" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Upload Media</h1>
                        <p className="text-muted-foreground">Upload images, documents, videos and audio files</p>
                    </div>
                    <Link href={route('admin.media.index')}>
                        <Button variant="outline">
                            Back to Media Library
                        </Button>
                    </Link>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Drag and Drop Area */}
                    <Card className="p-8">
                        <div
                            className={`border-2 border-dashed rounded-lg p-8 text-center transition-colors ${
                                isDragOver 
                                    ? 'border-blue-500 bg-blue-50' 
                                    : 'border-gray-300 hover:border-gray-400'
                            }`}
                            onDragOver={handleDragOver}
                            onDragLeave={handleDragLeave}
                            onDrop={handleDrop}
                        >
                            <Upload className="h-12 w-12 mx-auto mb-4 text-gray-400" />
                            <h3 className="text-lg font-semibold mb-2">
                                Drop files here or click to browse
                            </h3>
                            <p className="text-gray-600 mb-4">
                                Support for images, documents, videos, and audio files
                            </p>
                            <p className="text-sm text-gray-500 mb-4">
                                Maximum file size: 500MB per file, up to 10 files at once
                            </p>
                            
                            <input
                                ref={fileInputRef}
                                type="file"
                                multiple
                                accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,video/*,audio/*"
                                onChange={handleFileSelect}
                                className="hidden"
                            />
                            
                            <Button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                disabled={isUploading}
                            >
                                <Plus className="mr-2 h-4 w-4" />
                                Select Files
                            </Button>
                        </div>
                    </Card>

                    {/* File List */}
                    {files.length > 0 && (
                        <Card className="p-6">
                            <h3 className="text-lg font-semibold mb-4">Selected Files ({files.length})</h3>
                            <div className="space-y-4">
                                {files.map((file) => (
                                    <div key={file.id} className="flex items-center space-x-4 p-3 border rounded-lg">
                                        <div className="flex-shrink-0">
                                            {file.preview ? (
                                                <img 
                                                    src={file.preview} 
                                                    alt={file.name}
                                                    className="h-12 w-12 object-cover rounded"
                                                />
                                            ) : (
                                                getFileIcon(file)
                                            )}
                                        </div>
                                        
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="text-sm font-medium text-gray-900 truncate">
                                                        {file.name}
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        {getFileSize(file.size)} • {file.type || 'Unknown'}
                                                    </p>
                                                </div>
                                                
                                                <div className="flex items-center space-x-2">
                                                    {file.uploading && (
                                                        <Loader2 className="h-4 w-4 animate-spin text-blue-500" />
                                                    )}
                                                    {file.success && (
                                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                                    )}
                                                    {file.error && (
                                                        <AlertCircle className="h-4 w-4 text-red-500" />
                                                    )}
                                                    {!isUploading && (
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => removeFile(file.id)}
                                                        >
                                                            <X className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                            
                                            {file.uploading && (
                                                <div className="mt-2">
                                                    <Progress value={file.progress || 0} className="h-2" />
                                                    <p className="text-xs text-gray-500 mt-1">
                                                        {typeof file.progress === 'number' && !isNaN(file.progress) ? Math.round(file.progress) : 0}% uploaded
                                                    </p>
                                                </div>
                                            )}
                                            
                                            {file.error && (
                                                <p className="text-xs text-red-600 mt-1">{file.error}</p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </Card>
                    )}

                    {/* Upload Settings */}
                    {files.length > 0 && (
                        <Card className="p-6">
                            <h3 className="text-lg font-semibold mb-4">Upload Settings</h3>
                            <div className="space-y-4">
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_public"
                                        checked={data.is_public}
                                        onCheckedChange={(checked) => setData('is_public', checked as boolean)}
                                    />
                                    <Label htmlFor="is_public" className="text-sm font-medium">
                                        Make files publicly accessible
                                    </Label>
                                </div>
                                <p className="text-sm text-gray-500">
                                    Public files can be accessed by anyone with the URL. 
                                    Private files require authentication.
                                </p>
                            </div>
                        </Card>
                    )}

                    {/* Error Messages */}
                    {Object.keys(errors).length > 0 && (
                        <Alert>
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>
                                <ul className="list-disc list-inside space-y-1">
                                    {Object.entries(errors).map(([key, message]) => (
                                        <li key={key}>{message}</li>
                                    ))}
                                </ul>
                            </AlertDescription>
                        </Alert>
                    )}

                    {/* Submit Button */}
                    {files.length > 0 && (
                        <div className="flex justify-end space-x-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    setFiles([]);
                                    setData('files', []);
                                }}
                                disabled={isUploading}
                            >
                                Clear All
                            </Button>
                            <Button
                                type="submit"
                                disabled={isUploading || files.length === 0}
                                className="min-w-32"
                            >
                                {isUploading ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Uploading...
                                    </>
                                ) : (
                                    <>
                                        <Upload className="mr-2 h-4 w-4" />
                                        Upload {files.length} File{files.length !== 1 ? 's' : ''}
                                    </>
                                )}
                            </Button>
                        </div>
                    )}
                </form>

                {/* Help Text */}
                <Card className="p-4 bg-blue-50">
                    <h4 className="font-medium text-blue-900 mb-2">Supported File Types</h4>
                    <div className="text-sm text-blue-800 space-y-1">
                        <p><strong>Images:</strong> JPEG, PNG, GIF, WebP, SVG (max 10MB)</p>
                        <p><strong>Documents:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV (max 50MB)</p>
                        <p><strong>Videos:</strong> MP4, AVI, QuickTime, WebM (max 500MB)</p>
                        <p><strong>Audio:</strong> MP3, WAV, OGG, AAC (max 100MB)</p>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
};

export default MediaCreate;