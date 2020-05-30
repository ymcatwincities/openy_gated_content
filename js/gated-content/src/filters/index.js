const filters = [
  {
    name: 'capitalize',
    execute: (value) => {
      if (!value) return '';
      const newValue = value.toString().toLowerCase();
      return newValue.charAt(0).toUpperCase() + newValue.slice(1);
    }
  },
  {
    name: 'first_letter',
    execute: (value) => {
      if (!value) return '';
      return value.charAt(0).toUpperCase();
    }
  },
];

export default filters;
