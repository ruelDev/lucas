import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Eye } from "lucide-react";

export default function ViewUserDialog() {
    return (
        <Dialog>
            <DialogTrigger>
                <div className="flex items-center gap-2 cursor-pointer py-1 px-2 rounded-md hover:bg-blue-100 hover:text-blue-600">
                    <Eye className="h-4 w-4" />
                    <span className="text-sm">View</span>
                </div>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle> View User </DialogTitle>
                    <DialogDescription>
                        This action cannot be undone. This will permanently delete your account
                        and remove your data from our servers.
                    </DialogDescription>
                </DialogHeader>
            </DialogContent>
        </Dialog>
    );
}
