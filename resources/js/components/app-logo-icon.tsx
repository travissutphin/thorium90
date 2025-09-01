import { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            {...props}
            src="/images/logos/login-logo.png"
            alt="Thorium90"
            className="w-auto h-auto max-w-full max-h-32 object-contain mx-auto"
        />
    );
}
