/* eslint-disable react/prop-types */
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip';

export default function TruncatedText({ text, className = '', disabled = false, tooltipSide = 'top' }) {
    if (!text || disabled) {
        return <div className={`truncate ${className}`}>{text || '-'}</div>;
    }

    return (
        <TooltipProvider>
            <Tooltip delayDuration={300}>
                <TooltipTrigger asChild>
                    <div className={`truncate ${className} cursor-help`}>{text}</div>
                </TooltipTrigger>
                <TooltipContent side={tooltipSide} className="max-w-xs wrap-break-word">
                    <p>{text}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    );
}
