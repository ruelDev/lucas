/* eslint-disable react/prop-types */
import AreaSearch from "./area-search";
import DateFilter from "./date-filter";
import ExportBtn from "./export-btn";
import RoleSelect from "./role-select";

export default function DataTableControls({
    roleSelect,
    onRoleSelectChange,
    areaSearch,
    onAreaSearchChange,
    dateFrom,
    onDateFromChange,
    dateTo,
    onDateToChange,
    onExport
}) {
    return (
        <div className="mb-4 flex justify-between items-center gap-2">
            <div className="flex flex-1 items-center space-x-6">
                <RoleSelect roleSelect={roleSelect} onRoleSelectChange={onRoleSelectChange} />
                <AreaSearch areaSearch={areaSearch} onAreaSearchChange={onAreaSearchChange} />
                <DateFilter dateFrom={dateFrom} onDateFromChange={onDateFromChange} dateTo={dateTo} onDateToChange={onDateToChange} />
            </div>
            <div className="flex flex-1 justify-end items-center space-x-2">
                <ExportBtn onExport={onExport} />
            </div>
        </div>
    );
}
