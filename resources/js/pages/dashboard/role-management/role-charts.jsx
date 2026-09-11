/* eslint-disable react/prop-types */
import RoleCounts from './role-count';
import RoleUsers from './role-users';

export default function RoleCharts({ data }) {
    const { roles } = data;

    return (
        <div className="flex flex-col gap-4 lg:flex-row">
            <div className="lg:w-1/4">
                <RoleCounts roles={roles} />
            </div>

            <div className="lg:w-3/4">
                <RoleUsers roles={roles} />
            </div>
        </div>
    );
}
