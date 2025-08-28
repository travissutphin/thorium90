import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Checkbox } from "@/components/ui/checkbox";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { 
    Save,
    ArrowLeft,
    Image as ImageIcon,
    FileText,
    Video,
    Music,
    X,
    Plus,
    AlertCircle
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface MediaFile {
    id: number;
    filename: string;
    alt_text?: string;
    description?: string;
    tags?: string[];
    is_public: boolean;
    url: string;
    thumbnail_url?: string;
    type: string;
    is_image: boolean;
}

interface Props {
    media: MediaFile;
}

const MediaEdit = ({ media }: Props) => {
    const [newTag, setNewTag] = useState('');
    
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
        {
            title: 'Edit',
            href: `/admin/media/${media.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors } = useForm({
        alt_text: media.alt_text || '',
        description: media.description || '',
        tags: media.tags || [],
        is_public: media.is_public,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.media.update', { media: media.id }));
    };

    const addTag = () => {
        if (newTag.trim() && !data.tags.includes(newTag.trim())) {
            setData('tags', [...data.tags, newTag.trim()]);
            setNewTag('');
        }
    };

    const removeTag = (tagToRemove: string) => {
        setData('tags', data.tags.filter(tag => tag !== tagToRemove));
    };

    const handleTagKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag();
        }
    };

    const getFileIcon = (file: MediaFile) => {
        if (file.is_image) return <ImageIcon className="h-6 w-6" />;
        if (file.type === 'video') return <Video className="h-6 w-6" />;
        if (file.type === 'audio') return <Music className="h-6 w-6" />;
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Media: ${media.filename}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={route('admin.media.show', { media: media.id })}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Media
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">Edit Media</h1>
                            <div className="flex items-center gap-2 mt-1">
                                <Badge className={getFileTypeColor(media.type)}>
                                    {media.type}
                                </Badge>
                                <span className="text-muted-foreground">{media.filename}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Media Preview */}
                        <div className="lg:col-span-1">
                            <Card className="p-6">
                                <h2 className="text-lg font-semibold mb-4 flex items-center gap-2">
                                    {getFileIcon(media)}
                                    Media Preview
                                </h2>
                                
                                <div className="flex justify-center bg-muted rounded-lg p-6">
                                    {media.is_image ? (
                                        <img
                                            src={media.thumbnail_url || media.url}
                                            alt={data.alt_text || media.filename}
                                            className="max-w-full max-h-48 object-contain rounded-lg shadow-sm"
                                        />
                                    ) : (
                                        <div className="text-center">
                                            {getFileIcon(media)}
                                            <p className="text-sm text-muted-foreground mt-2">
                                                {media.filename}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </Card>
                        </div>

                        {/* Edit Form */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Basic Information */}
                            <Card className="p-6">
                                <h3 className="text-lg font-semibold mb-4">Media Information</h3>
                                <div className="space-y-4">
                                    {/* Alt Text - Only for images */}
                                    {media.is_image && (
                                        <div>
                                            <Label htmlFor="alt_text">Alt Text</Label>
                                            <Input
                                                id="alt_text"
                                                value={data.alt_text}
                                                onChange={(e) => setData('alt_text', e.target.value)}
                                                placeholder="Describe this image for accessibility"
                                                className="mt-1"
                                            />
                                            <p className="text-sm text-muted-foreground mt-1">
                                                Alt text is important for accessibility and SEO. 
                                                Describe what's in the image for users who can't see it.
                                            </p>
                                            {errors.alt_text && (
                                                <p className="text-sm text-red-600 mt-1">{errors.alt_text}</p>
                                            )}
                                        </div>
                                    )}

                                    {/* Description */}
                                    <div>
                                        <Label htmlFor="description">Description</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Add a description for this media file"
                                            rows={4}
                                            className="mt-1"
                                        />
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Optional description to help organize and search for this media file.
                                        </p>
                                        {errors.description && (
                                            <p className="text-sm text-red-600 mt-1">{errors.description}</p>
                                        )}
                                    </div>

                                    {/* Visibility */}
                                    <div>
                                        <div className="flex items-center space-x-2">
                                            <Checkbox
                                                id="is_public"
                                                checked={data.is_public}
                                                onCheckedChange={(checked) => setData('is_public', checked as boolean)}
                                            />
                                            <Label htmlFor="is_public" className="text-sm font-medium">
                                                Make this file publicly accessible
                                            </Label>
                                        </div>
                                        <p className="text-sm text-muted-foreground mt-1">
                                            Public files can be accessed by anyone with the URL. 
                                            Private files require authentication to view.
                                        </p>
                                        {errors.is_public && (
                                            <p className="text-sm text-red-600 mt-1">{errors.is_public}</p>
                                        )}
                                    </div>
                                </div>
                            </Card>

                            {/* Tags */}
                            <Card className="p-6">
                                <h3 className="text-lg font-semibold mb-4">Tags</h3>
                                
                                {/* Current Tags */}
                                {data.tags.length > 0 && (
                                    <div className="mb-4">
                                        <p className="text-sm font-medium mb-2">Current Tags:</p>
                                        <div className="flex flex-wrap gap-2">
                                            {data.tags.map((tag, index) => (
                                                <Badge key={index} variant="outline" className="text-sm">
                                                    {tag}
                                                    <button
                                                        type="button"
                                                        onClick={() => removeTag(tag)}
                                                        className="ml-2 hover:text-red-600"
                                                    >
                                                        <X className="h-3 w-3" />
                                                    </button>
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Add New Tag */}
                                <div>
                                    <Label htmlFor="new_tag">Add Tags</Label>
                                    <div className="flex gap-2 mt-1">
                                        <Input
                                            id="new_tag"
                                            value={newTag}
                                            onChange={(e) => setNewTag(e.target.value)}
                                            onKeyPress={handleTagKeyPress}
                                            placeholder="Enter a tag and press Enter"
                                            className="flex-1"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={addTag}
                                            disabled={!newTag.trim()}
                                        >
                                            <Plus className="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <p className="text-sm text-muted-foreground mt-1">
                                        Tags help organize and search for media files. 
                                        Press Enter or click the plus button to add a tag.
                                    </p>
                                    {errors.tags && (
                                        <p className="text-sm text-red-600 mt-1">{errors.tags}</p>
                                    )}
                                </div>
                            </Card>
                        </div>
                    </div>

                    {/* Error Messages */}
                    {Object.keys(errors).length > 0 && (
                        <Alert>
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>
                                <p className="font-medium mb-1">Please fix the following errors:</p>
                                <ul className="list-disc list-inside space-y-1">
                                    {Object.entries(errors).map(([key, message]) => (
                                        <li key={key} className="text-sm">{message}</li>
                                    ))}
                                </ul>
                            </AlertDescription>
                        </Alert>
                    )}

                    {/* Submit Button */}
                    <div className="flex justify-end space-x-4">
                        <Link href={route('admin.media.show', { media: media.id })}>
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="min-w-32"
                        >
                            <Save className="mr-2 h-4 w-4" />
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
};

export default MediaEdit;