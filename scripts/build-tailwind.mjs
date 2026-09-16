/**
 * Compiles resources/css/app.css into a single, self-contained stylesheet at
 * public/css/tailwind.css so the app can be served without Vite or any CDN.
 *
 * Usage: node scripts/build-tailwind.mjs [--minify]
 */
import { compile, optimize } from '@tailwindcss/node';
import { Scanner } from '@tailwindcss/oxide';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const inputPath = resolve(projectRoot, 'resources/css/app.css');
const outputPath = resolve(projectRoot, 'public/css/tailwind.css');
const minify = process.argv.includes('--minify');

const input = await readFile(inputPath, 'utf8');
const compiler = await compile(input, {
    base: dirname(inputPath),
    onDependency() {},
});

const roots =
    compiler.root === 'none'
        ? []
        : compiler.root === null
          ? [{ base: projectRoot, pattern: '**/*', negated: false }]
          : [{ base: compiler.root.base, pattern: compiler.root.pattern, negated: false }];

const scanner = new Scanner({ sources: [...roots, ...compiler.sources] });
let css = compiler.build(scanner.scan());
css = optimize(css, { minify }).code;

await mkdir(dirname(outputPath), { recursive: true });
await writeFile(outputPath, css);

console.log(`Wrote ${outputPath} (${(css.length / 1024).toFixed(1)} KB)`);
