/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { FileOutput } from 'lucide-react';
import * as React from 'react';

import InputError from '@/components/input-error';
import useSwalOptions from '@/hooks/useSwalOptions';
import { router } from '@inertiajs/react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export function SendBack({ table }) {
    const [isRemarksOpen, setIsRemarksOpen] = React.useState(false);
    const [isCheckedAll, setIsCheckedAll] = React.useState(false);
    const [itemRemarks, setItemRemarks] = React.useState({});
    const [processing, setProcessing] = React.useState(false);
    const [errors, setErrors] = React.useState('');

    const [remarks, setRemarks] = React.useState(table.getSelectedRowModel().rows.map((row) => row.original.remarks | ''));
    const ReactSwal = withReactContent(Swal);

    const selectedItems = table.getSelectedRowModel().rows.map((row) => row.original);

    const { swalLoading, onSuccess } = useSwalOptions();

    const onSubmit = async (e) => {
        e.preventDefault();
        setProcessing(true);

        if (selectedItems.length == 0) {
            ReactSwal.fire({
                title: 'No rows selected',
                text: 'Please select at least one row',
                icon: 'warning',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            return;
        }

        router.post(
            route('out-collection.authorization.send-back'),
            {
                items: selectedItems,
                remarks: isCheckedAll ? remarks : null,
                itemRemarks: isCheckedAll ? null : itemRemarks,
                isCheckedAll: isCheckedAll,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onStart: () => swalLoading('Sending Back'),
                onSuccess: (res) => {
                    onSuccess(res);
                    table.resetRowSelection();
                    setIsRemarksOpen(false);
                    setRemarks('');
                    setItemRemarks({});
                    setProcessing(false);
                },
                onError: (error) => {
                    setErrors(error);

                    setProcessing(false);

                    ReactSwal.close();
                },
            },
        );
    };

    return (
        <Dialog open={isRemarksOpen} onOpenChange={(result) => setIsRemarksOpen(result)}>
            <DialogTrigger asChild>
                <Button
                    disabled={selectedItems.length == 0}
                    className="h-10 bg-linear-to-r from-red-900 to-red-800 text-white transition duration-300 hover:from-red-950 hover:to-red-900 disabled:cursor-not-allowed"
                >
                    <FileOutput className="h-4 w-4" />
                    Send Back ({selectedItems.length})
                </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl pr-2">
                <form onSubmit={onSubmit}>
                    <DialogHeader>
                        <DialogTitle>Send Back</DialogTitle>
                        <DialogDescription>Add remarks for selected ({selectedItems.length}) items</DialogDescription>
                    </DialogHeader>
                    <ScrollArea className={`max-h-[50vh] pr-3 pb-2 ${!isCheckedAll ? 'h-[50vh]' : ''}`}>
                        <FieldGroup className="p-2">
                            <Separator />
                            <Field orientation="horizontal">
                                <Checkbox
                                    checked={isCheckedAll}
                                    onCheckedChange={(checked) => setIsCheckedAll(checked)}
                                    id="checkbox-remark"
                                    name="checkbox-remark"
                                />
                                <FieldLabel htmlFor="checkbox-remark">Use same remarks for all selected ({selectedItems.length}) items</FieldLabel>
                            </Field>
                            <Separator />
                            {isCheckedAll ? (
                                <Field>
                                    <FieldLabel htmlFor="textarea-remark">Remarks</FieldLabel>
                                    <FieldDescription>Enter your remarks below.</FieldDescription>
                                    <Textarea
                                        value={remarks}
                                        onChange={(e) => setRemarks(e.target.value)}
                                        id="textarea-remark"
                                        placeholder="Type your remarks here."
                                    />
                                    <InputError message={errors.remarks} />
                                </Field>
                            ) : (
                                selectedItems.map((item) => (
                                    <Field key={item.id}>
                                        <FieldLabel htmlFor={`textarea-remark-${item.id}`}>
                                            <span className="text-muted-foreground">RN:</span> {item.referenceNumber}
                                        </FieldLabel>
                                        <Textarea
                                            value={itemRemarks[item.id] || ''}
                                            onChange={(e) =>
                                                setItemRemarks({
                                                    ...itemRemarks,
                                                    [item.id]: e.target.value,
                                                })
                                            }
                                            id={`textarea-remark-${item.id}`}
                                            placeholder="Type your remarks here."
                                        />
                                        <InputError message={errors.itemRemarks} />
                                    </Field>
                                ))
                            )}
                        </FieldGroup>
                    </ScrollArea>
                    <DialogFooter className="pr-3">
                        <DialogClose asChild>
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button type="submit" disabled={processing} className="submit-button disabled:cursor-not-allowed">
                            Send back ({selectedItems.length})
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
