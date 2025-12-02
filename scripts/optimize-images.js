#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const SOURCE_DIR = path.join(__dirname, '..', 'public', 'images');
const OUTPUT_DIR = SOURCE_DIR;
const SUPPORTED = ['.png', '.jpg', '.jpeg'];

async function processFile(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  if (!SUPPORTED.includes(ext)) {
    return;
  }

  const relPath = path.relative(SOURCE_DIR, filePath);
  const outputWebp = path.join(OUTPUT_DIR, relPath.replace(ext, '.webp'));

  console.log(`Optimisation ${relPath} → ${path.relative(SOURCE_DIR, outputWebp)}`);

  await sharp(filePath)
    .webp({ quality: 80 })
    .toFile(outputWebp);
}

async function walk(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      await walk(fullPath);
    } else {
      await processFile(fullPath);
    }
  }
}

walk(SOURCE_DIR)
  .then(() => console.log('Optimisation terminée.'))
  .catch((error) => {
    console.error('Erreur optimisation images', error);
    process.exit(1);
  });
