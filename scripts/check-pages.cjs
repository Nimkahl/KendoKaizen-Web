const {chromium}=require('playwright');
const assert=require('node:assert/strict');
(async()=>{
const browser=await chromium.launch({headless:true});const page=await browser.newPage({viewport:{width:1440,height:1000}});let errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.goto((process.env.KK_PREVIEW_URL || 'http://127.0.0.1:9413/'),{waitUntil:'networkidle'});
await page.screenshot({path:'/tmp/kk-pages-home.png',fullPage:true});
assert(await page.locator('.kk-calendar-panel').isVisible()); assert.equal(await page.locator('.kk-contact').count(),0);assert.equal(await page.locator('[data-carousel]').count(),1);
const homeIds=await page.locator('.kk-card [data-event-id]').evaluateAll(es=>es.map(e=>e.dataset.eventId));
await page.getByRole('navigation').getByRole('link',{name:'Archive',exact:true}).click();await page.waitForLoadState('networkidle');
assert(await page.locator('.kk-list-panel').isVisible());assert.equal(await page.locator('.kk-previous-events').isVisible(),false);
const archiveIds=await page.locator('.kk-archive-row').evaluateAll(es=>es.map(e=>e.dataset.eventId)); assert(archiveIds.length);assert(!archiveIds.some(id=>homeIds.includes(id)));
const dates=await page.evaluate(()=>{const data=JSON.parse(document.querySelector('#kk-event-data').textContent);return [...document.querySelectorAll('.kk-archive-row')].map(e=>data.find(d=>String(d.id)===e.dataset.eventId).start)});assert.deepEqual(dates,[...dates].sort().reverse());
await page.screenshot({path:'/tmp/kk-pages-archive.png',fullPage:true});
await page.locator('.kk-archive-row').first().click(); assert(await page.locator('.kk-modal').isVisible());await page.keyboard.press('Escape');
await page.locator('#kk-country-filter').selectOption('France');assert((await page.locator('.kk-archive-row:visible').count())>0);await page.locator('#kk-country-filter').selectOption('');
assert.equal(await page.locator('[data-view]').count(),0);
await page.getByRole('navigation').getByRole('link',{name:'Contact',exact:true}).click();await page.waitForLoadState('networkidle');assert.equal(await page.locator('.kk-contact form').count(),2);assert.equal(await page.locator('.kk-app').count(),0);
await page.screenshot({path:'/tmp/kk-pages-contact.png',fullPage:true});
for(const name of ['Home','Archive','Contact']) {await page.setViewportSize({width:390,height:844});await page.locator('.kk-mobile-menu-button').click();await page.getByRole('navigation').getByRole('link',{name,exact:true}).click();await page.waitForLoadState('networkidle');assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),name+' overflow');await page.screenshot({path:'/tmp/kk-pages-'+name.toLowerCase()+'-mobile.png',fullPage:true});}
assert.deepEqual(errors,[]);console.log(JSON.stringify({home:homeIds.length,archive:archiveIds.length,errors}));await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
