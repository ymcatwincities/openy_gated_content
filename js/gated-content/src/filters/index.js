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
    name: 'simplePluralize',
    execute: (singular, number) => {
      if (parseInt(number, 10) === 1) {
        return singular;
      }

      return `${singular}s`;
    },
  },
  {
    name: 'prependZero',
    execute: (number) => {
      if (number < 10) {
        return `0${number}`;
      }

      return number;
    },
  },
  {
    name: 'hour',
    execute: (value) => {
      const hoursValue = value % 12 || 12;
      const morningValue = value < 12 ? 'a.m.' : 'p.m.';
      return `${hoursValue} ${morningValue}`;
    },
  },
  {
    name: 'day',
    execute: (value) => new Date(value).getDate(),
  },
  {
    name: 'weekday',
    execute: (value) => new Date(value).toLocaleDateString('en', { weekday: 'long' }),
  },
  {
    name: 'weekday_short',
    execute: (value) => new Date(value).toLocaleDateString('en', { weekday: 'short' }),
  },
  {
    name: 'month_short',
    execute: (value) => {
      const date = new Date(value);
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
      ];

      return monthNames[date.getMonth()];
    },
  },
  {
    name: 'month',
    execute: (value) => new Date(value).toLocaleDateString('en', { month: 'long' }),
  },
  {
    name: 'start_and_duration',
    execute: (date) => {
      if (!date) return '';

      const dateStart = new Date(date.value);
      const dateEnd = new Date(date.end_value);
      const startHours = dateStart.getHours() % 12 || 12;
      const startMinutes = (dateStart.getMinutes() < 10 ? '0' : '') + dateStart.getMinutes();
      const startMorning = dateStart.getHours() < 12 ? 'a.m.' : 'p.m.';
      const duration = Math.abs(dateEnd - dateStart) / 1000 / 60;

      return `${startHours}:${startMinutes} ${startMorning} (${duration} mins)`;
    },
  },
];

export default filters;
