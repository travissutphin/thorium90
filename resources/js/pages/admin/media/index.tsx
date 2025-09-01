import { Head, Link, router } from '@inertiajs/react';
import { useState, useCallback } from 'react';
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { 
    Upload, 
    Search, 
    Filter, 
    Image, 
    FileText, 
    Video, 
    Music, 
    Trash2, 
    Edit, 
    Eye,
    Download,
    MoreHorizontal,
    FileType
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
// Pagination component to be implemented later
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Checkbox } from "@/components/ui/checkbox";

interface MediaFile {
    id: number;
    filename: string;
    stored_filename: string;
    mime_type: string;
    extension: string;
    size: number;
    human_size: string;
    type: string;
    metadata?: any;
    alt_text?: string;
    description?: string;
    tags?: string[];
    is_public: boolean;
    url: string;
    thumbnail_url?: string;
    is_image: boolean;
    is_document: boolean;
    is_video: boolean;
    is_audio: boolean;
    uploader: {
        id: number;
        name: string;
        email: string;
    };
    created_at: string;
    updated_at: string;
}

interface MediaStats {
    total_files: number;
    total_size: number;
    images_count: number;
    documents_count: number;
    videos_count: number;
    audio_count: number;
    clean_files: number;
    pending_scan: number;
}

interface Props {
    media: {
        data: MediaFile[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    stats: MediaStats;
    filters: {
        type?: string;
        uploader?: string;
        search?: string;
    };
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
];

const MediaIndex = ({ media, stats, filters }: Props) => {
    const [selectedFiles, setSelectedFiles] = useState<number[]>([]);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || 'all');

    const handleSearch = useCallback((e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('admin.media.index'), {
            search: searchTerm,
            type: typeFilter === 'all' ? '' : typeFilter,
        }, { preserveState: true });
    }, [searchTerm, typeFilter]);

    const handleSelectFile = (fileId: number, checked: boolean) => {
        if (checked) {
            setSelectedFiles(prev => [...prev, fileId]);
        } else {
            setSelectedFiles(prev => prev.filter(id => id !== fileId));
        }
    };

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedFiles(media.data.map(file => file.id));
        } else {
            setSelectedFiles([]);
        }
    };

    const handleBulkDelete = () => {
        if (selectedFiles.length === 0) return;
        
        if (confirm(`Are you sure you want to delete ${selectedFiles.length} selected files?`)) {
            router.post(route('admin.media.bulk-action'), {
                action: 'delete',
                media_ids: selectedFiles
            });
            setSelectedFiles([]);
        }
    };

    const getFileIcon = (file: MediaFile) => {
        if (file.is_image) return <Image className="h-12 w-12 text-green-600" />;
        if (file.is_video) return <Video className="h-12 w-12 text-purple-600" />;
        if (file.is_audio) return <Music className="h-12 w-12 text-orange-600" />;
        
        // PDF specific icon
        if (file.mime_type === 'application/pdf' || file.extension?.toLowerCase() === 'pdf') {
            return <FileType className="h-12 w-12 text-red-600" />;
        }
        
        // Excel files - Green icons
        if (['xls', 'xlsx'].includes(file.extension?.toLowerCase() || '') || 
            ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(file.mime_type)) {
            return <FileText className="h-12 w-12 text-green-600" />;
        }
        
        // Word documents - Blue icons (doc, docx)
        if (['doc', 'docx'].includes(file.extension?.toLowerCase() || '') || 
            ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.mime_type)) {
            return <FileText className="h-12 w-12 text-blue-600" />;
        }
        
        // Default document icon - gray
        return <FileText className="h-12 w-12 text-gray-600" />;
    };

    const getFileTypeColor = (type: string) => {
        switch (type) {
            case 'image': return 'bg-green-100 text-green-800';
            case 'document': return 'bg-blue-100 text-blue-800';
            case 'video': return 'bg-purple-100 text-purple-800';
            case 'audio': return 'bg-orange-100 text-orange-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const formatFileSize = (bytes: number) => {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Media Library" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Media Library</h1>
                        <p className="text-muted-foreground">Manage your uploaded files and media assets</p>
                    </div>
                    <Link href={route('admin.media.create')}>
                        <Button>
                            <Upload className="mr-2 h-4 w-4" />
                            Upload Files
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <div className="p-6">
                            <div className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <div className="text-sm font-medium">Total Files</div>
                            </div>
                            <div className="text-2xl font-bold">{stats.total_files}</div>
                        </div>
                    </Card>
                    <Card>
                        <div className="p-6">
                            <div className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <div className="text-sm font-medium">Images</div>
                                <Image className="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div className="text-2xl font-bold">{stats.images_count}</div>
                        </div>
                    </Card>
                    <Card>
                        <div className="p-6">
                            <div className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <div className="text-sm font-medium">Documents</div>
                                <FileText className="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div className="text-2xl font-bold">{stats.documents_count}</div>
                        </div>
                    </Card>
                    <Card>
                        <div className="p-6">
                            <div className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <div className="text-sm font-medium">Storage Used</div>
                            </div>
                            <div className="text-2xl font-bold">{formatFileSize(stats.total_size)}</div>
                        </div>
                    </Card>
                </div>

                {/* Search and Filters */}
                <Card>
                    <div className="p-6">
                        <form onSubmit={handleSearch} className="flex gap-4 items-end">
                        <div className="flex-1">
                            <Input
                                placeholder="Search files..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="w-full"
                            />
                        </div>
                        <div className="w-48">
                            <Select value={typeFilter} onValueChange={setTypeFilter}>
                                <SelectTrigger>
                                    <SelectValue placeholder="File type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Types</SelectItem>
                                    <SelectItem value="image">Images</SelectItem>
                                    <SelectItem value="document">Documents</SelectItem>
                                    <SelectItem value="video">Videos</SelectItem>
                                    <SelectItem value="audio">Audio</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button type="submit">
                            <Search className="h-4 w-4 mr-2" />
                            Search
                        </Button>
                        </form>
                    </div>
                </Card>

                {/* Bulk Actions */}
                {selectedFiles.length > 0 && (
                    <Card>
                        <div className="p-4">
                            <div className="flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    {selectedFiles.length} file(s) selected
                                </p>
                                <div className="flex gap-2">
                                    <Button variant="destructive" size="sm" onClick={handleBulkDelete}>
                                        <Trash2 className="h-4 w-4 mr-2" />
                                        Delete Selected
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </Card>
                )}

                {/* Media Grid */}
                <Card>
                    <div className="p-6 border-b">
                        <div className="flex items-center">
                            <Checkbox
                                checked={selectedFiles.length === media.data.length && media.data.length > 0}
                                onCheckedChange={handleSelectAll}
                            />
                            <span className="ml-3 text-sm font-medium">Select All</span>
                        </div>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-6">
                        {media.data.map((file) => (
                            <div key={file.id} className="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                <div className="aspect-square bg-muted flex items-center justify-center">
                                    {file.is_image ? (
                                        <img
                                            src={file.thumbnail_url || file.url}
                                            alt={file.alt_text || file.filename}
                                            className="w-full h-full object-cover"
                                            onLoad={() => {
                                                console.log(`Image loaded successfully for ${file.filename}`);
                                            }}
                                            onError={(e) => {
                                                console.log(`Image failed to load for ${file.filename}`);
                                                console.log('Thumbnail URL:', file.thumbnail_url);
                                                console.log('Main URL:', file.url);
                                                console.log('Current src:', e.currentTarget.src);
                                                
                                                // If thumbnail fails, try main URL
                                                if (e.currentTarget.src !== file.url) {
                                                    console.log('Trying main URL...');
                                                    e.currentTarget.src = file.url;
                                                } else {
                                                    console.log('Both thumbnail and main URL failed');
                                                    // If main URL also fails, show placeholder
                                                    e.currentTarget.style.display = 'none';
                                                    if (e.currentTarget.nextSibling) {
                                                        (e.currentTarget.nextSibling as HTMLElement).style.display = 'flex';
                                                    }
                                                }
                                            }}
                                        />
                                    ) : null}
                                    {!file.is_image && (
                                        <div className="text-muted-foreground">
                                            {getFileIcon(file)}
                                        </div>
                                    )}
                                    
                                    <div className="absolute top-2 left-2">
                                        <Checkbox
                                            checked={selectedFiles.includes(file.id)}
                                            onCheckedChange={(checked) => handleSelectFile(file.id, checked as boolean)}
                                        />
                                    </div>
                                </div>
                                
                                <div className="p-3">
                                    <div className="flex items-center justify-between mb-2">
                                        <Badge className={getFileTypeColor(file.type)}>
                                            {file.type}
                                        </Badge>
                                        <span className="text-xs text-muted-foreground">{file.human_size}</span>
                                    </div>
                                    
                                    <h3 className="font-medium text-sm truncate" title={file.filename}>
                                        {file.filename}
                                    </h3>
                                    
                                    <p className="text-xs text-muted-foreground mb-2">
                                        By {file.uploader.name}
                                    </p>
                                    
                                    <div className="flex items-center justify-between">
                                        <div className="flex gap-1">
                                            <Link href={route('admin.media.show', { media: file.id })}>
                                                <Button variant="ghost" size="sm">
                                                    <Eye className="h-3 w-3" />
                                                </Button>
                                            </Link>
                                            <Link href={route('admin.media.edit', { media: file.id })}>
                                                <Button variant="ghost" size="sm">
                                                    <Edit className="h-3 w-3" />
                                                </Button>
                                            </Link>
                                        </div>
                                        
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal className="h-3 w-3" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DropdownMenuItem>
                                                    <a href={file.url} download>
                                                        <Download className="h-4 w-4 mr-2" />
                                                        Download
                                                    </a>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    className="text-red-600"
                                                    onClick={() => {
                                                        if (confirm('Are you sure you want to delete this file?')) {
                                                            router.delete(route('admin.media.destroy', { media: file.id }));
                                                        }
                                                    }}
                                                >
                                                    <Trash2 className="h-4 w-4 mr-2" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                    
                    {media.data.length === 0 && (
                        <div className="p-8 text-center text-muted-foreground">
                            <Upload className="h-12 w-12 mx-auto mb-4 text-muted-foreground/50" />
                            <h3 className="text-lg font-medium mb-2">No files found</h3>
                            <p className="mb-4">Upload your first media file to get started.</p>
                            <Link href={route('admin.media.create')}>
                                <Button>Upload Files</Button>
                            </Link>
                        </div>
                    )}
                </Card>

                {/* Pagination */}
                {media.last_page > 1 && (
                    <Card>
                        <div className="p-4">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Showing {media.from} to {media.to} of {media.total} results
                                </div>
                                <div className="flex space-x-2">
                                    {media.current_page > 1 && (
                                        <Link
                                            href={route('admin.media.index', { ...filters, page: media.current_page - 1 })}
                                        >
                                            <Button variant="outline" size="sm">Previous</Button>
                                        </Link>
                                    )}
                                    {media.current_page < media.last_page && (
                                        <Link
                                            href={route('admin.media.index', { ...filters, page: media.current_page + 1 })}
                                        >
                                            <Button variant="outline" size="sm">Next</Button>
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
};

export default MediaIndex;