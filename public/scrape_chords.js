const puppeteer = require('puppeteer');

const url = process.argv[2];
if (!url) {
    console.error('❌ Please provide a URL as an argument.');
    process.exit(1);
}

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115 Safari/537.36'
        );

        await page.goto(url, { waitUntil: 'networkidle2', timeout: 0 });
        await page.waitForSelector('pre', { timeout: 15000 });
        const allPre = await page.$$eval('pre', elements =>
            elements.map(el => el.innerText.trim()).join('\n\n')
        );

        if (allPre) {
            console.log(allPre);
        } else {
            console.log('❌ No chords found in <pre> tags.');
        }

    } catch (err) {
        console.error('🚨 Error scraping chords:', err.message);
    } finally {
        await browser.close();
    }
})();
