const {test}=require('node:test');
const assert=require('node:assert/strict');
const {build}=require('../wp-content/themes/kendokaizen/calendar-export.js');
const base={id:12,title:'Kendō, seminar; exam',start:'2026-12-31',end:'2027-01-02',country:'Serbia',calendarTiming:{allDay:true},price:'€25',schedule:'First\nSecond',examGrades:'1–3 Dan'};
const make=(e=base)=>build(e,'Description\nNext paragraph','https://example.com/#event=test',new Date('2026-11-10T12:00:00Z'));
test('inclusive multi-day dates exported with exclusive end, all fields and escaped text',()=>{
 const r=make();assert.match(r.ics,/DTSTART;VALUE=DATE:20261231/);assert.match(r.ics,/DTEND;VALUE=DATE:20270103/);
 assert.match(r.ics,/SUMMARY:Kendō\\, seminar\\; exam/);assert.match(r.description,/Fees: €25/);assert.match(r.description,/Exam grades: 1–3 Dan/);
 assert.equal(new URL(r.links.google).searchParams.get('dates'),'20261231/20270103');
 assert.equal(new URL(r.links.outlook).searchParams.get('allday'),'true');
});
test('single day and leap day',()=>{assert.match(make({...base,start:'2028-02-29',end:'2028-02-29'}).ics,/DTEND;VALUE=DATE:20280301/);});
test('timed UTC across DST preserved in all providers',()=>{
 const r=make({...base,calendarTiming:{allDay:false,startUtc:'2026-10-25T00:30:00Z',endUtc:'2026-10-25T02:30:00Z',timeZone:'Europe/Madrid'}});
 assert.match(r.ics,/DTSTART:20261025T003000Z/);assert.match(r.ics,/DTEND:20261025T023000Z/);
 assert.equal(new URL(r.links.office).searchParams.get('enddt'),'2026-10-25T02:30:00.000Z');
});
test('UTF-8 folding is at most 75 bytes and lossless',()=>{
 const r=make({...base,title:'日本語 🥋'.repeat(80)});
 for(const line of r.ics.split('\r\n'))assert(Buffer.byteLength(line)<=75);
 assert(r.ics.replace(/\r\n /g,'').includes('SUMMARY:'+'日本語 🥋'.repeat(80)));
});
test('invalid dates and missing timed values rejected',()=>{
 assert.throws(()=>make({...base,start:'2026-02-30'}));assert.throws(()=>make({...base,end:'2026-01-01'}));
 assert.throws(()=>make({...base,calendarTiming:{allDay:false}}));
});
