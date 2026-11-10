"""Patch only the timeline theme files into a preserved Playground export."""
import argparse
import pathlib
import zipfile

parser = argparse.ArgumentParser()
parser.add_argument('original', type=pathlib.Path)
parser.add_argument('output', type=pathlib.Path)
args = parser.parse_args()
if args.original.resolve() == args.output.resolve():
    parser.error('The output must differ from the original export.')
root = pathlib.Path(__file__).resolve().parent.parent
theme = 'wp-content/themes/kendokaizen/'
replacements = {theme + name: (root / theme / name).read_bytes()
                for name in ('app.js', 'functions.php', 'style.css', 'timeline.js')}
for asset in (root / theme / 'assets/pdf').glob('*'):
    if asset.is_file():
        replacements[asset.relative_to(root).as_posix()] = asset.read_bytes()
args.output.parent.mkdir(parents=True, exist_ok=True)
with zipfile.ZipFile(args.original) as source, zipfile.ZipFile(args.output, 'w', zipfile.ZIP_DEFLATED) as target:
    existing = set(source.namelist())
    for entry in source.infolist():
        target.writestr(entry, replacements.get(entry.filename, source.read(entry.filename)))
    for name in replacements.keys() - existing:
        target.writestr(name, replacements[name])
with zipfile.ZipFile(args.original) as source, zipfile.ZipFile(args.output) as target:
    assert target.testzip() is None
    for name in source.namelist():
        if name not in replacements:
            assert source.read(name) == target.read(name), name
    for name, content in replacements.items():
        assert target.read(name) == content, name
print(f'Created {args.output}: original database, importer and all other entries preserved.')
