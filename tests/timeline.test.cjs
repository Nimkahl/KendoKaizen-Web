const { test } = require('node:test');
const assert = require('node:assert/strict');
const timeline = require('../wp-content/themes/kendokaizen/timeline.js');
const event = (id, start, end = start, types = ['Seminar']) => ({ id, title: `Event ${id}`, start, end, types });

test('one-day events occupy one day; dates sort independently of input order', () => {
  const result = timeline.model([event(2, '2026-11-12'), event(1, '2026-11-10')], 2026, 10);
  assert.deepEqual(result.rows.map(row => row.event.id), [1, 2]);
  assert.equal(result.rows[0].length, 1);
  assert.equal(result.rows[0].offset, 9);
  assert.equal(result.days, 92);
});
test('clips both boundaries inclusively and excludes events outside period', () => {
  const result = timeline.model([event(1, '2026-10-30', '2026-11-02'), event(2, '2027-01-31', '2027-02-02'), event(3, '2027-02-01'), event(4, '2026-10-31')], 2026, 10);
  assert.deepEqual(result.rows.map(row => [row.offset, row.length]), [[0, 2], [91, 1]]);
});
test('handles leap years, multi-day spans and year changes', () => {
  assert.equal(timeline.model([event(1, '2028-02-28', '2028-03-01')], 2028, 1).rows[0].length, 3);
  assert.equal(timeline.model([event(1, '2026-12-31', '2027-01-02')], 2026, 11).rows[0].length, 3);
});
test('rejects invalid dates and reversed ranges; handles missing end and empty input', () => {
  assert.equal(timeline.model([event(1, '2026-02-30'), event(2, 'bad'), event(3, '2026-02-05', '2026-02-01')], 2026, 1).rows.length, 0);
  assert.equal(timeline.model([{ ...event(1, '2026-11-10'), end: '' }], 2026, 10).rows[0].length, 1);
  assert.equal(timeline.model([], 2026, 10).rows.length, 0);
});
test('fixed colour order with deduplication for one, two and three categories', () => {
  assert.deepEqual(timeline.typesFor({ types: ['Grading', 'Seminar', 'Competition', 'Seminar'] }).map(type => type.color), ['#17365D', '#287A48', '#454545']);
  assert.equal(timeline.typesFor({ types: ['Competition', 'Grading'] }).length, 2);
  assert.equal(timeline.typesFor({ types: ['Seminar'] }).length, 1);
  assert.equal(timeline.typesFor({ types: ['Training camp', 'Seminar'] }).length, 1);
});
test('country flags and unknown-country fallback', () => {
  assert.equal(timeline.flagFor('Spain'), '🇪🇸');
  assert.equal(timeline.flagFor('FR'), '🇫🇷');
  assert.equal(timeline.flagFor('Czech Republic'), '🇨🇿');
  assert.equal(timeline.flagFor('United Kingdom'), '🇬🇧');
  assert.equal(timeline.flagFor(''), '🌐');
  assert.equal(timeline.flagFor('Unknown'), '🌐');
});
