#!/bin/bash
set -e  # Exit on error

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}ProjectSend Release Builder${NC}"
echo "=============================="
echo ""

# Prompt for version number
read -p "Enter version number (e.g., 1720): " VERSION_NUMBER
if [ -z "$VERSION_NUMBER" ]; then
    echo -e "${RED}Error: Version number cannot be empty${NC}"
    exit 1
fi

# Validate version number (only digits)
if ! [[ "$VERSION_NUMBER" =~ ^[0-9]+$ ]]; then
    echo -e "${RED}Error: Version number must contain only digits${NC}"
    exit 1
fi

VERSION="r${VERSION_NUMBER}"
echo -e "${GREEN}Creating release: ${VERSION}${NC}"
echo ""

# Detect if we're in the ProjectSend directory
if [ -f "./bootstrap.php" ]; then
    # Running from inside ProjectSend directory
    SOURCE_DIR="$(pwd)"
    PARENT_DIR="$(dirname "$SOURCE_DIR")"
    RELEASE_DIR="$PARENT_DIR/projectsend-release"
    echo "Detected ProjectSend directory: $SOURCE_DIR"
elif [ -d "./projectsend.local" ] && [ -f "./projectsend.local/bootstrap.php" ]; then
    # Running from parent directory with projectsend.local subdirectory
    SOURCE_DIR="$(pwd)/projectsend.local"
    RELEASE_DIR="$(pwd)/projectsend-release"
    echo "Detected parent directory with projectsend.local/"
else
    echo -e "${RED}Error: Could not find ProjectSend installation${NC}"
    echo "Please run this script either from:"
    echo "  - The ProjectSend root directory (containing bootstrap.php)"
    echo "  - The parent directory containing projectsend.local/"
    exit 1
fi

# Clean up any existing release directory
if [ -d "$RELEASE_DIR" ]; then
    echo -e "${YELLOW}Cleaning up previous release...${NC}"
    rm -rf "$RELEASE_DIR"
fi

echo -e "${GREEN}Step 1: Copying source files${NC}"
echo "Source: $SOURCE_DIR"
echo "Release: $RELEASE_DIR"
cp -r "$SOURCE_DIR" "$RELEASE_DIR"
cd "$RELEASE_DIR"

echo -e "${GREEN}Step 2: Installing production dependencies${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}Step 3: Building production assets${NC}"
npm ci --silent
npx gulp build

echo -e "${GREEN}Step 4: Updating version number${NC}"
# Update CURRENT_VERSION in app.php
sed -i "s/define('CURRENT_VERSION', 'r[0-9]\+');/define('CURRENT_VERSION', '${VERSION}');/g" includes/app.php
echo "Updated version to: ${VERSION}"

echo -e "${GREEN}Step 5: Cleaning cache${NC}"
rm -rf ./cache/*
mkdir -p ./cache
touch ./cache/.gitkeep

echo -e "${GREEN}Step 6: Removing configuration files${NC}"
rm -f ./includes/sys.config.php

echo -e "${GREEN}Step 7: Removing Git directories${NC}"
rm -rf .git
rm -rf .github

echo -e "${GREEN}Step 8: Removing development files${NC}"
# Remove hidden directories
rm -rf .claude .ignore .vscode .idea

# Remove development documentation (keep only README.md and SECURITY.md in root)
find . -maxdepth 1 -type f -name "*.md" ! -name "README.md" ! -name "SECURITY.md" -delete

# Remove development .md files from subdirectories
find . -type f \( \
    -name "*_PLAN.md" \
    -o -name "*_GUIDE.md" \
    -o -name "*_IMPLEMENTATION.md" \
    -o -name "ROADMAP.md" \
    -o -name "WHATS_NEW.md" \
    -o -name "CLAUDE.md" \
    -o -name "PHPSTAN.md" \
\) -delete

# Remove PHPStan files
rm -f phpstan.neon phpstan-baseline.neon phpstan-bootstrap.php

# Remove development directories
rm -rf docs_temp results reports

# Remove make-release.sh itself
rm -f make-release.sh

echo -e "${GREEN}Step 9: Cleaning upload directories${NC}"

# Clean upload/temp
cd ./upload/temp
mkdir ../tmp && cp index.php ../tmp && cp .htaccess ../tmp && cp web.config ../tmp
rm -rf ./*
mv ../tmp/index.php . && mv ../tmp/.htaccess . && mv ../tmp/web.config .
rm -rf ../tmp

# Clean upload/admin
cd ../admin
mkdir ../tmp && cp index.php ../tmp
rm -rf ./*
mv ../tmp/* .
rm -rf ../tmp

# Clean upload/files
cd ../files
mkdir ../tmp && cp index.php ../tmp && cp .htaccess ../tmp && cp web.config ../tmp
rm -rf ./*
mv ../tmp/index.php . && mv ../tmp/.htaccess . && mv ../tmp/web.config .
rm -rf ../tmp

# Clean upload/thumbnails
cd ../thumbnails
mkdir ../tmp && cp index.php ../tmp
rm -rf ./*
mv ../tmp/* .
rm -rf ../tmp

echo -e "${GREEN}Step 10: Creating ZIP archive${NC}"
cd ../../
ZIPFILE="projectsend-${VERSION}.zip"
zip -r -q "$ZIPFILE" *

echo -e "${GREEN}Step 11: Generating SHA256 hash${NC}"
SHA256=$(sha256sum "$ZIPFILE" | cut -d' ' -f1)

echo -e "${GREEN}Step 12: Moving release file${NC}"
OUTPUT_DIR="$(dirname "$RELEASE_DIR")"
mv "$ZIPFILE" "$OUTPUT_DIR/"
OUTPUT_FILE="$OUTPUT_DIR/$ZIPFILE"

echo -e "${GREEN}Step 13: Cleaning up temporary files${NC}"
rm -rf "$RELEASE_DIR"

echo ""
echo -e "${GREEN}✓ Release created successfully!${NC}"
echo "=============================="
echo -e "File: ${YELLOW}$OUTPUT_FILE${NC}"
echo -e "Size: ${YELLOW}$(du -h "$OUTPUT_FILE" | cut -f1)${NC}"
echo -e "SHA256: ${YELLOW}${SHA256}${NC}"
echo ""
echo -e "${GREEN}Release is ready for distribution!${NC}"
