export function useDate() {
    //  e.g. Jan 01, 2025
    const formatMonthShort = (date) => {
        return date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', }) : '-';
    };

    //  e.g. 08:00 AM
    const formatTime = (date) => {
        return date ? new Date(date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '-';
    };

    //  e.g. 2025-10-11
    const formatAllNumeric = (date) => {
        return date?.toLocaleString('en-CA', { month: 'numeric', day: 'numeric', year: 'numeric'}) || '-';
    };

    //  e.g. 2025-10
    const formatMonthYear = (year, month) => {
        return year && month ? new Date(year, month).toLocaleString('en-CA', { month: 'numeric', year: 'numeric'}) : '-';
    };

    //  e.g. Jan 01, 2025, 08:00 AM
    const formatDateTime = (date) => {
        return date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) : '-';
    };


    return { formatMonthShort, formatTime, formatAllNumeric, formatMonthYear, formatDateTime };
  }