import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { 
    Download,
    Edit,
    Trash2,
    ArrowLeft,
    Image as ImageIcon,
    FileText,
    Video,
    Music,
    User,
    Calendar,
    HardDrive,
    Shield,
    ShieldAlert,
    Clock
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface MediaFile {
    id: number;
    filename: string;
    stored_filename: string;
    path: string;
    mime_type: string;
    extension: string;
    size: number;
    human_size: string;
    type: string;
    metadata?: {
        width?: number;
        height?: number;
        aspect_ratio?: number;
        [key: string]: any;
    };
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
    is_clean: boolean;
    is_infected: boolean;
    scan_pending: boolean;
    uploader: {
        id: number;
        name: string;
        email: string;
        avatar_url?: string;
    };
    scanned_at?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    media: MediaFile;
}

const MediaShow = ({ media }: Props) => {
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
            title: media.filename,
            href: `/admin/media/${media.id}`,
        },
    ];

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this media file? This action cannot be undone.')) {
            router.delete(route('admin.media.destroy', { media: media.id }));
        }
    };

    const getFileIcon = (file: MediaFile) => {
        if (file.is_image) return <ImageIcon className="h-6 w-6" />;
        if (file.is_video) return <Video className="h-6 w-6" />;
        if (file.is_audio) return <Music className="h-6 w-6" />;
        return <FileText className="h-6 w-6" />;
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

    const getScanStatusBadge = () => {
        if (media.scan_pending) {
            return (
                <Badge variant="secondary" className="bg-yellow-100 text-yellow-800">
                    <Clock className="mr-1 h-3 w-3" />
                    Scan Pending
                </Badge>
            );
        }
        if (media.is_clean) {
            return (
                <Badge variant="secondary" className="bg-green-100 text-green-800">
                    <Shield className="mr-1 h-3 w-3" />
                    Clean
                </Badge>
            );
        }
        if (media.is_infected) {
            return (
                <Badge variant="destructive">
                    <ShieldAlert className="mr-1 h-3 w-3" />
                    Infected
                </Badge>
            );
        }
        return null;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Media: ${media.filename}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={route('admin.media.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Media Library
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">{media.filename}</h1>
                            <div className="flex items-center gap-2 mt-1">
                                <Badge className={getFileTypeColor(media.type)}>
                                    {media.type}
                                </Badge>
                                {getScanStatusBadge()}
                                <Badge variant={media.is_public ? "secondary" : "outline"}>
                                    {media.is_public ? "Public" : "Private"}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <a href={media.url} download>
                            <Button variant="outline">
                                <Download className="h-4 w-4 mr-2" />
                                Download
                            </Button>
                        </a>
                        <Link href={route('admin.media.edit', { media: media.id })}>
                            <Button variant="outline">
                                <Edit className="h-4 w-4 mr-2" />
                                Edit
                            </Button>
                        </Link>
                        <Button variant="destructive" onClick={handleDelete}>
                            <Trash2 className="h-4 w-4 mr-2" />
                            Delete
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Media Preview */}
                    <div className="lg:col-span-2">
                        <Card className="p-6">
                            <h2 className="text-lg font-semibold mb-4 flex items-center gap-2">
                                {getFileIcon(media)}
                                Media Preview
                            </h2>
                            
                            <div className="flex justify-center bg-muted rounded-lg p-8">
                                {media.is_image ? (
                                    <img
                                        src={media.url}
                                        alt={media.alt_text || media.filename}
                                        className="max-w-full max-h-96 object-contain rounded-lg shadow-lg"
                                    />
                                ) : media.is_video ? (
                                    <video
                                        src={media.url}
                                        controls
                                        className="max-w-full max-h-96 rounded-lg shadow-lg"
                                    >
                                        Your browser does not support the video tag.
                                    </video>
                                ) : media.is_audio ? (
                                    <div className="text-center">
                                        <Music className="h-24 w-24 mx-auto mb-4 text-muted-foreground" />
                                        <audio controls className="w-full max-w-md">
                                            <source src={media.url} type={media.mime_type} />
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                ) : (
                                    <div className="text-center">
                                        <FileText className="h-24 w-24 mx-auto mb-4 text-muted-foreground" />
                                        <p className="text-muted-foreground">
                                            Preview not available for this file type
                                        </p>
                                        <a href={media.url} target="_blank" rel="noopener noreferrer">
                                            <Button className="mt-4">View File</Button>
                                        </a>
                                    </div>
                                )}
                            </div>
                        </Card>

                        {/* Description and Tags */}
                        {(media.description || media.tags?.length) && (
                            <Card className="p-6 mt-6">
                                <h3 className="text-lg font-semibold mb-4">Details</h3>
                                
                                {media.alt_text && (
                                    <div className="mb-4">
                                        <h4 className="font-medium text-sm mb-1">Alt Text</h4>
                                        <p className="text-sm text-muted-foreground">{media.alt_text}</p>
                                    </div>
                                )}

                                {media.description && (
                                    <div className="mb-4">
                                        <h4 className="font-medium text-sm mb-1">Description</h4>
                                        <p className="text-sm text-muted-foreground">{media.description}</p>
                                    </div>
                                )}

                                {media.tags?.length && (
                                    <div>
                                        <h4 className="font-medium text-sm mb-2">Tags</h4>
                                        <div className="flex flex-wrap gap-1">
                                            {media.tags.map((tag, index) => (
                                                <Badge key={index} variant="outline" className="text-xs">
                                                    {tag}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* File Info */}
                        <Card className="p-6">
                            <h3 className="text-lg font-semibold mb-4">File Information</h3>
                            <dl className="space-y-3">
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">File Name</dt>
                                    <dd className="text-sm break-all">{media.filename}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">File Size</dt>
                                    <dd className="text-sm flex items-center gap-2">
                                        <HardDrive className="h-4 w-4" />
                                        {media.human_size}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Type</dt>
                                    <dd className="text-sm">{media.mime_type}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Extension</dt>
                                    <dd className="text-sm uppercase">{media.extension}</dd>
                                </div>
                                {media.metadata?.width && media.metadata?.height && (
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">Dimensions</dt>
                                        <dd className="text-sm">
                                            {media.metadata.width} × {media.metadata.height} pixels
                                        </dd>
                                    </div>
                                )}
                                {media.metadata?.aspect_ratio && (
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">Aspect Ratio</dt>
                                        <dd className="text-sm">{media.metadata.aspect_ratio}:1</dd>
                                    </div>
                                )}
                            </dl>
                        </Card>

                        {/* Upload Info */}
                        <Card className="p-6">
                            <h3 className="text-lg font-semibold mb-4">Upload Information</h3>
                            <dl className="space-y-3">
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Uploaded By</dt>
                                    <dd className="text-sm flex items-center gap-2">
                                        <User className="h-4 w-4" />
                                        <div>
                                            <div>{media.uploader.name}</div>
                                            <div className="text-xs text-muted-foreground">{media.uploader.email}</div>
                                        </div>
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Upload Date</dt>
                                    <dd className="text-sm flex items-center gap-2">
                                        <Calendar className="h-4 w-4" />
                                        {new Date(media.created_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}
                                    </dd>
                                </div>
                                {media.scanned_at && (
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">Last Scanned</dt>
                                        <dd className="text-sm flex items-center gap-2">
                                            <Shield className="h-4 w-4" />
                                            {new Date(media.scanned_at).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </dd>
                                    </div>
                                )}
                            </dl>
                        </Card>

                        {/* Direct Link */}
                        <Card className="p-6">
                            <h3 className="text-lg font-semibold mb-4">Direct Link</h3>
                            <div className="bg-muted p-3 rounded text-sm break-all">
                                {media.url}
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                className="mt-2 w-full"
                                onClick={() => navigator.clipboard.writeText(media.url)}
                            >
                                Copy URL
                            </Button>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default MediaShow;