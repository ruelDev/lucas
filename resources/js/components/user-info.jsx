import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { useMemo } from 'react';

export function UserInfo({ user, showEmail = false }) {
    const getInitials = useInitials();

    const fullname = useMemo(() => {
        const { fname, mname, lname } = user;
        return `${fname} ${mname ? mname + ' ' : ' '} ${lname}`
    }, [user]);

    const profilePictureUrl = useMemo(() => {
        return user.profile_picture ? `/storage/profile_pictures/${user.profile_picture}` : null;
    }, [user.profile_picture]);

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={profilePictureUrl} alt={fullname} />
                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                    {getInitials(fullname)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{fullname}</span>
                {showEmail && <span className="text-muted-foreground truncate text-xs">{user.email}</span>}
            </div>
        </>
    );
}
