// list-php-files.cjs
const fs = require("fs");
const path = require("path");

function parseArgs() {
  const args = process.argv.slice(2);
  const opts = { dir: process.cwd(), out: "php-files.txt", print: false };
  for (const a of args) {
    if (a === "--print") opts.print = true;
    else if (a.startsWith("--dir=")) opts.dir = path.resolve(a.split("=")[1]);
    else if (a.startsWith("--out=")) opts.out = path.resolve(a.split("=")[1]);
  }
  return opts;
}

const EXCLUDES = new Set([
  "vendor",
  "node_modules",
  "storage",
  ".git",
  "bootstrap",
]);

function shouldSkipDir(fullPath, entryName) {
  if (EXCLUDES.has(entryName)) return true;
  // Cas particulier: ignorer bootstrap/cache
  if (entryName === "bootstrap") return true;
  return false;
}

function listPhpFiles(dir, onFile) {
  let entries;
  try {
    entries = fs.readdirSync(dir, { withFileTypes: true });
  } catch (e) {
    console.error("Impossible de lire le dossier:", dir, "-", e.message);
    return;
  }

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (shouldSkipDir(fullPath, entry.name)) continue;
      listPhpFiles(fullPath, onFile);
    } else if (entry.isFile() && entry.name.toLowerCase().endsWith(".php")) {
      onFile(fullPath);
    }
  }
}

function main() {
  const { dir, out, print } = parseArgs();

  if (!print) {
    try {
      // Vider le fichier de sortie s’il existe
      fs.writeFileSync(out, "");
    } catch (e) {
      console.error("Erreur lors de la préparation du fichier de sortie:", e.message);
      process.exit(1);
    }
  }

  let count = 0;
  console.log(`🔎 Scan du dossier: ${dir}`);

  listPhpFiles(dir, (filePath) => {
    count++;
    let content = "";
    try {
      content = fs.readFileSync(filePath, "utf8");
    } catch (e) {
      const msg = `❌ Erreur lecture: ${filePath} - ${e.message}\n`;
      if (print) process.stdout.write(msg);
      else fs.appendFileSync(out, msg);
      return;
    }

    const block =
      "====================================================\n" +
      `📄 Fichier : ${filePath}\n` +
      "----------------------------------------------------\n" +
      content +
      "\n\n";

    if (print) {
      process.stdout.write(block);
    } else {
      fs.appendFileSync(out, block);
    }
  });

  if (print) {
    console.log(`\n✅ Terminé. ${count} fichier(s) .php listé(s).`);
  } else {
    console.log(`✅ Terminé. ${count} fichier(s) .php listé(s) → ${out}`);
  }
}

main();
