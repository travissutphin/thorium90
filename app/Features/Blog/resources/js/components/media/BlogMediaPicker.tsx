import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Search, Image, FileText, Video, Music, Loader2, Check } from 'lucide-react';
import { router } from '@inertiajs/react';

interface MediaFile {
    id: number;
    filename: string;
    mime_type: string;
    size: number;
    human_size: string;
    type: string;
    alt_text?: string;
    description?: string;
    url: string;
    thumbnail_url?: string;
    is_image: boolean;
    is_video: boolean;
    is_audio: boolean;
    is_document: boolean;
    created_at: string;
}

interface MediaResponse {
    success: boolean;
    data: MediaFile[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
}

interface BlogMediaPickerProps {
    isOpen: boolean;
    onClose: () => void;
    onSelect: (media: MediaFile) => void;
    selectedId?: number;
}

export default function BlogMediaPicker({ isOpen, onClose, onSelect, selectedId }: BlogMediaPickerProps) {
    const [media, setMedia] = useState<MediaFile[]>([]);
    const [loading, setLoading] = useState(false);
    const [search, setSearch] = useState('');
    const [currentPage, setCurrent


] = useState(1);
    const [meta, setMeta] = useState<MediaResponse['meta']>({
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
        from: null,
        to: null,
    });

    const fetchMedia = async (page = 1, searchQuery = '') => {
        setLoading(true);
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                per_page: '12',
                type: 'image', // Blog featured images should be images
                ...(searchQuery && { search: searchQuery }),
            });

            const response = await fetch(`/admin/blog/media/picker?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load media');
            }

            const result: MediaResponse = await response.json();
            
            if (result.success) {
                setMedia(result.data);
                setMeta(result.meta);
                setCurrent


(result.meta.current_page);
            }
        } catch (error) {
            console.error('Error fetching media:', error);
            // TODO: Show error toast notification
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (isOpen) {
            fetchMedia(1, search);
        }
    }, [isOpen]);

    const handleSearch = () => {
        setCurrent


(1);
        fetchMedia(1, search);
    };

    const handlePageChange = (page: number) => {
        fetchMedia(page, search);
    };

    const getMediaIcon = (media: MediaFile) => {
        if (media.is_image) return <Image className="h-4 w-4" />;
        if (media.is_video) return <Video className="h-4 w-4" />;
        if (media.is_audio) return <Music className="h-4 w-4" />;
        return <FileText className="h-4 w-4" />;
    };

    const getMediaTypeColor = (media: MediaFile) => {
        if (media.is_image) return 'bg-blue-100 text-blue-800';
        if (media.is_video) return 'bg-purple-100 text-purple-800';
        if (media.is_audio) return 'bg-green-100 text-green-800';
        return 'bg-gray-100 text-gray-800';
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-4xl h-[80vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle>Select Featured Image</DialogTitle>
                    <DialogDescription>
                        Choose an image from your media library for the blog post featured image.
                    </DialogDescription>
                </DialogHeader>

                <div className="flex gap-2 mb-4">
                    <div className="flex-1 relative">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input
                            placeholder="Search images..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                            className="pl-10"
                        />
                    </div>
                    <Button onClick={handleSearch} disabled={loading}>
                        {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : 'Search'}
                    </Button>
                </div>

                <div className="flex-1 overflow-y-auto">
                    {loading && media.length === 0 ? (
                        <div className="flex items-center justify-center h-32">
                            <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                            <span className="ml-2 text-muted-foreground">Loading media...</span>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            {media.map((item) => (
                                <Card
                                    key={item.id}
                                    className={`cursor-pointer transition-all hover:shadow-md ${
                                        selectedId === item.id ? 'ring-2 ring-primary' : ''
                                    }`}
                                    onClick={() => onSelect(item)}
                                >
                                    <CardContent className="p-2">
                                        <div className="relative aspect-square mb-2 rounded overflow-hidden bg-muted">
                                            {item.is_image ? (
                                                <img
                                                    src={item.thumbnail_url || item.url}
                                                    alt={item.alt_text || item.filename}
                                                    className="w-full h-full object-cover"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center">
                                                    {getMediaIcon(item)}
                                                </div>
                                            )}
                                            
                                            {selectedId === item.id && (
                                                <div className="absolute inset-0 bg-primary/10 flex items-center justify-center">
                                                    <div className="bg-primary text-primary-foreground rounded-full p-1">
                                                        <Check className="h-4 w-4" />
                                                    </div>
                                                </div>
                                            )}

                                            <Badge
                                                variant="secondary"
                                                className={`absolute top-1 right-1 text-xs ${getMediaTypeColor(item)}`}
                                            >
                                                {item.type}
                                            </Badge>
                                        </div>
                                        
                                        <div className="space-y-1">
                                            <p className="text-xs font-medium truncate" title={item.filename}>
                                                {item.filename}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {item.human_size}
                                            </p>
                                            {item.alt_text && (
                                                <p className="text-xs text-muted-foreground truncate" title={item.alt_text}>
                                                    {item.alt_text}
                                                </p>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}

                    {media.length === 0 && !loading && (
                        <div className="text-center py-8 text-muted-foreground">
                            <Image className="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>No images found</p>
                            <p className="text-sm">Try adjusting your search terms</p>
                        </div>
                    )}
                </div>

                {meta.total > 0 && (
                    <div className="flex items-center justify-between pt-4 border-t">
                        <div className="text-sm text-muted-foreground">
                            Showing {meta.from}-{meta.to} of {meta.total} images
                        </div>
                        
                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(currentPage - 1)}
                                disabled={currentPage <= 1 || loading}
                            >
                                Previous
                            </Button>
                            
                            <div className="flex items-center gap-2">
                                <span className="text-sm">
                                    Page {currentPage} of {meta.last_page}
                                </span>
                            </div>
                            
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => handlePageChange(currentPage + 1)}
                                disabled={currentPage >= meta.last_page || loading}
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}