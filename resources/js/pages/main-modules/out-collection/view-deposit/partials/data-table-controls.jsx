/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import { canAny } from '@/lib/can';
import Authorize from '../actions/authorize';
import AddNewDeposit from '../actions/create';
import { SendBack } from '../actions/send-back';
import SendToAuthor from '../actions/send-to-author';

export const DataTableControls = ({ searchTerm, onSearchChange, deposits, payments, table }) => {
    const hasPaymentSentBack = payments.some((payment) => payment.status.includes('Sent Back'));

    const canCreate = canAny(['out_collection.create']);
    const canAuthorize = canAny(['out_collection.authorize']);

    const isNotSentToAuthor = deposits.status != 'Sent to Author';
    const isNotAuthorized = deposits.status != 'Authorized';
    const isDraftOrSentBack = (!hasPaymentSentBack && deposits.status == 'Sent Back') || deposits.status == 'Draft';

    const showAddNewDeposit = canCreate && deposits.remaining >= 0 && isNotSentToAuthor && isNotAuthorized;

    const showSendToAuthor = canCreate && deposits.remaining <= 0 && isNotSentToAuthor && isNotAuthorized && isDraftOrSentBack;

    const showAuthorActions = canAuthorize && deposits.status == 'Sent to Author';

    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex flex-1 items-center gap-2">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="All columns..." />
            </div>
            <div className="flex items-center gap-2">
                {/* maker */}
                {showAddNewDeposit && <AddNewDeposit payments={deposits} />}
                {showSendToAuthor && <SendToAuthor data={deposits} />}

                {/* author */}
                {showAuthorActions && (
                    <>
                        <SendBack table={table} />
                        <Authorize data={deposits} />
                    </>
                )}
            </div>
        </div>
    );
};
