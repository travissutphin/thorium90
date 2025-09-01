import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Image, Link as LinkIcon, Trash2 } from 'lucide-react';
import BlogMediaPicker from '../media/BlogMediaPicker';

interface MediaFile {
    id: number;
    filename: string;
    url: string;
    alt_text?: string;
    type: string;
}

interface BlogFeaturedImageSelectorProps {
    imageUrl: string;
    altText: string;
    onChange: (url: string, altText: string) => void;
    error?: string;
}

export default function BlogFeaturedImageSelector({ 
    imageUrl, 
    altText, 
    onChange, 
    error 
}: BlogFeaturedImageSelectorProps) {
    const [isPickerOpen, setIsPickerOpen] = useState(false);
    const [selectedMediaId, setSelectedMediaId] = useState<number>();

    const handleMediaSelect = (media: MediaFile) => {
        onChange(media.url, media.alt_text || '');
        setSelectedMediaId(media.id);
        setIsPickerOpen(false);
    };

    const handleUrlChange = (url: string) => {
        onChange(url, altText);
        setSelectedMediaId(undefined); // Clear media selection when URL is manually set
    };

    const handleAltTextChange = (alt: string) => {
        onChange(imageUrl, alt);
    };

    const handleClear = () => {
        onChange('', '');
        setSelectedMediaId(undefined);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Image className="h-5 w-5" />
                    Featured Image
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                <Tabs defaultValue="url" className="w-full">
                    <TabsList className="grid w-full grid-cols-2">
                        <TabsTrigger value="url" className="flex items-center gap-2">
                            <LinkIcon className="h-4 w-4" />
                            URL Entry
                        </TabsTrigger>
                        <TabsTrigger value="media" className="flex items-center gap-2">
                            <Image className="h-4 w-4" />
                            Media Library
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="url" className="space-y-4">
                        <div>
                            <Label htmlFor="featured_image_url">Image URL</Label>
                            <div className="flex gap-2">
                                <Input
                                    id="featured_image_url"
                                    type="url"
                                    value={imageUrl}
                                    onChange={(e) => handleUrlChange(e.target.value)}
                                    placeholder="https://example.com/image.jpg"
                                    className={error ? 'border-destructive' : ''}
                                />
                                {imageUrl && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        onClick={handleClear}
                                        title="Clear image"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                            {error && (
                                <p className="text-sm text-destructive mt-1">{error}</p>
                            )}
                        </div>
                    </TabsContent>

                    <TabsContent value="media" className="space-y-4">
                        <div>
                            <Label>Select from Media Library</Label>
                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setIsPickerOpen(true)}
                                    className="flex-1"
                                >
                                    <Image className="h-4 w-4 mr-2" />
                                    {imageUrl ? 'Change Image' : 'Browse Media'}
                                </Button>
                                {imageUrl && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        onClick={handleClear}
                                        title="Clear image"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                )}
                            </div>
                            {selectedMediaId && (
                                <p className="text-xs text-muted-foreground mt-1">
                                    Selected from media library (ID: {selectedMediaId})
                                </p>
                            )}
                        </div>
                    </TabsContent>
                </Tabs>

                {/* Alt Text Field (shared across both tabs) */}
                <div>
                    <Label htmlFor="featured_image_alt">Alt Text</Label>
                    <Input
                        id="featured_image_alt"
                        value={altText}
                        onChange={(e) => handleAltTextChange(e.target.value)}
                        placeholder="Describe the image for accessibility"
                    />
                    <p className="text-xs text-muted-foreground mt-1">
                        Alternative text for screen readers and SEO
                    </p>
                </div>

                {/* Image Preview */}
                {imageUrl && (
                    <div className="space-y-2">
                        <Label>Preview</Label>
                        <div className="relative rounded-lg border overflow-hidden bg-muted">
                            <img
                                src={imageUrl}
                                alt={altText || 'Featured image preview'}
                                className="w-full h-auto max-h-64 object-cover"
                                onError={(e) => {
                                    e.currentTarget.style.display = 'none';
                                    const parent = e.currentTarget.parentElement;
                                    if (parent) {
                                        parent.innerHTML = `
                                            <div class="flex items-center justify-center h-32 text-muted-foreground">
                                                <div class="text-center">
                                                    <Image class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                                    <p class="text-sm">Failed to load image</p>
                                                </div>
                                            </div>
                                        `;
                                    }
                                }}
                            />
                            
                            {/* Image info overlay */}
                            {altText && (
                                <div className="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-2">
                                    <p className="text-sm truncate">{altText}</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Media Picker Modal */}
                <BlogMediaPicker
                    isOpen={isPickerOpen}
                    onClose={() => setIsPickerOpen(false)}
                    onSelect={handleMediaSelect}
                    selectedId={selectedMediaId}
                />
            </CardContent>
        </Card>
    );
}