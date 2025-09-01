import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    const [inputMethod, setInputMethod] = useState<'url' | 'media'>('url');

    const handleMediaSelect = (media: MediaFile) => {
        onChange(media.url, media.alt_text || '');
        setSelectedMediaId(media.id);
        setIsPickerOpen(false);
    };

    const handleUrlChange = (url: string) => {
        onChange(url, altText);
        setSelectedMediaId(undefined);
    };

    const handleAltTextChange = (alt: string) => {
        onChange(imageUrl, alt);
    };

    const handleClear = () => {
        onChange('', '');
        setSelectedMediaId(undefined);
    };

    return (
        <div className="border rounded-lg shadow-sm">
            <div className="px-6 py-3 border-b bg-muted/50">
                <h3 className="text-lg font-semibold flex items-center gap-2">
                    <Image className="h-5 w-5" />
                    Featured Image
                </h3>
            </div>
            <div className="p-6 space-y-4">
                {/* Input Method Toggle - Completely isolated buttons */}
                <div className="flex gap-2 mb-4">
                    <div
                        className={`px-3 py-2 text-sm font-medium rounded-md flex items-center gap-2 transition-colors cursor-pointer select-none ${
                            inputMethod === 'url' 
                                ? 'bg-blue-600 text-white' 
                                : 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700'
                        }`}
                        onClick={() => {
                            console.log('URL Entry clicked - isolated handler');
                            setInputMethod('url');
                        }}
                        onMouseDown={(e) => e.preventDefault()}
                    >
                        <LinkIcon className="h-4 w-4" />
                        URL Entry
                    </div>
                    <div
                        className={`px-3 py-2 text-sm font-medium rounded-md flex items-center gap-2 transition-colors cursor-pointer select-none ${
                            inputMethod === 'media' 
                                ? 'bg-blue-600 text-white' 
                                : 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700'
                        }`}
                        onClick={() => {
                            console.log('Media Library clicked - isolated handler');
                            setInputMethod('media');
                        }}
                        onMouseDown={(e) => e.preventDefault()}
                    >
                        <Image className="h-4 w-4" />
                        Media Library
                    </div>
                </div>

                {/* URL Entry Method */}
                {inputMethod === 'url' && (
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
                                <div
                                    className="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 w-9 cursor-pointer"
                                    onClick={() => {
                                        console.log('Clear button clicked');
                                        handleClear();
                                    }}
                                    title="Clear image"
                                    onMouseDown={(e) => e.preventDefault()}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </div>
                            )}
                        </div>
                        {error && (
                            <p className="text-sm text-destructive mt-1">{error}</p>
                        )}
                    </div>
                )}

                {/* Media Library Method */}
                {inputMethod === 'media' && (
                    <div>
                        <Label>Select from Media Library</Label>
                        <div className="flex gap-2">
                            <div
                                className="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 flex-1 cursor-pointer"
                                onClick={() => {
                                    console.log('Browse Media clicked');
                                    setIsPickerOpen(true);
                                }}
                                onMouseDown={(e) => e.preventDefault()}
                            >
                                <Image className="h-4 w-4 mr-2" />
                                {imageUrl ? 'Change Image' : 'Browse Media'}
                            </div>
                            {imageUrl && (
                                <div
                                    className="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 w-9 cursor-pointer"
                                    onClick={() => {
                                        console.log('Clear button clicked from media tab');
                                        handleClear();
                                    }}
                                    title="Clear image"
                                    onMouseDown={(e) => e.preventDefault()}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </div>
                            )}
                        </div>
                        {selectedMediaId && (
                            <p className="text-xs text-muted-foreground mt-1">
                                Selected from media library (ID: {selectedMediaId})
                            </p>
                        )}
                    </div>
                )}

                {/* Alt Text Field */}
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
                                                    <div class="h-8 w-8 mx-auto mb-2 opacity-50 bg-gray-300 rounded"></div>
                                                    <p class="text-sm">Failed to load image</p>
                                                </div>
                                            </div>
                                        `;
                                    }
                                }}
                            />
                            
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
            </div>
        </div>
    );
}