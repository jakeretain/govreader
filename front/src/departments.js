class Department {
    constructor(id, name, colour, url){
        this.id     = id;
        this.name   = name;
        this.colour = colour;
        this.url    = url;
    }
}

const departments = [
    new Department('AGO', 'Attorney General\'s Office', '#9f1888'),
    new Department('CO', 'Cabinet Office', '#005abb'),
    new Department('DBEIS', 'Department for Business, Energy & Industrial Strategy', '#003479'),
    new Department('DDCMS', 'Department for Digital, Culture, Media & Sport', '#d40072'),
    new Department('DE', 'Department for Digital, Culture, Media & Sport', '#003a69'),
    new Department('DEFRA', 'Department for Environment, Food & Rural Affairs', '#00a33b'),
    new Department('DID', 'Department for International Development', '#002878'),
    new Department('DIT', 'Department for International Trade', '#cf102d'),
    new Department('DT', 'Department for Transport', '#006c56'),
    new Department('DWP', 'Department for Work & Pensions', '#00beb7'),
    new Department('DHSC', 'Department for Health & Social Care', '#00ad93'),
    new Department('FCO', 'Foreign & Commonwealth Office', '#003e74'),
    new Department('HMT', 'HM Treasury', '#af292e'),
    new Department('HO', 'Home Office', '#9325b2'),
    new Department('MO', 'Ministry of Defence', '#4d2942'),
    new Department('MHCLG', 'Ministry of Housing, Communities & Local Government', '#099'),
    new Department('MOJ', 'Ministry of Justice', '#0b0c0c'),
    new Department('NIO', 'Northern Ireland Office', '#002663'),
    new Department('OAGS', 'Office of the Advocate General of Scotland', '#002663'),
    new Department('OLHC', 'Office of the Leader of the House of Commons', '#317023'),
    new Department('OLHL', 'Office of the Leader of the House of Lords', '#9c132e'),
    new Department('OSSS', 'Office of the Secretary of State for Scotland', '#002663'),
    new Department('OSSW', 'Office of the Secretary of State for Wales', '#a33038'),
    new Department('UKEF', 'UK Export Finance', '#cf102d')
];

export default departments