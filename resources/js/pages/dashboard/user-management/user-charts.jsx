/* eslint-disable react/prop-types */
import UserCounts from "./user-counts";
import UserSessions from "./user-sessions";

export default function UserCharts({ data }) {
    const userData = data.users

    return (
        <div className="flex flex-col space-y-4">
            <UserCounts userData={userData} />
            <UserSessions userData={userData} />
        </div>
    );
}
