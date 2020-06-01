const filters = [
  {
    name: 'capitalize',
    execute: (value) => {
      if (!value) return '';
      const newValue = value.toString().toLowerCase();
      return newValue.charAt(0).toUpperCase() + newValue.slice(1);
    },
  },
  {
    name: 'first_letter',
    execute: (value) => {
      if (!value) return '';
      return value.charAt(0).toUpperCase();
    },
  },
  {
    name: 'schedule',
    execute: (date) => {
      if (!date) return '';

      const dateStart = new Date(date.value);
      const dateEnd = new Date(date.end_value);
      const startHours = dateStart.getHours() % 12 || 12;
      const startMinutes = (dateStart.getMinutes() < 10 ? '0' : '') + dateStart.getMinutes();
      const startMorning = dateStart.getHours() < 12 ? 'a.m.' : 'p.m.';
      const endHours = dateEnd.getHours() % 12 || 12;
      const endMinutes = (dateEnd.getMinutes() < 10 ? '0' : '') + dateEnd.getMinutes();
      const endMorning = dateEnd.getHours() < 12 ? 'a.m.' : 'p.m.';
      const now = new Date();

      let start = `${startHours}:${startMinutes} ${startMorning} - `;

      if (dateStart < now && now < dateEnd) {
        start = 'Until ';
      }

      return `${start} ${endHours}:${endMinutes} ${endMorning}`;
    },
  },
];

export default filters;
