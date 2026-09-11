/* eslint-disable react/prop-types */
import MasterSetupCounts from './master-setup-counts';
import MasterSetupUsers from './master-setup-users';

export default function MasterSetupCharts({ data }) {
    const masterSetupData = data.masterSetup;

    return (
        <div className="flex flex-col gap-4 lg:flex-row">
            <div className="lg:w-1/2">
                <MasterSetupCounts masterSetupData={masterSetupData} />
            </div>

            <div className="lg:w-1/2">
                <MasterSetupUsers masterSetupData={masterSetupData} />
            </div>
        </div>
    );
}
