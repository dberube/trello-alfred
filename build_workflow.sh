#!/bin/bash

# Copy the icon to top level
cp -p src/images/icon.png .

zip -r trello.alfredworkflow LICENSE README.md CONTRIBUTING.md icon.png info.plist src

# Remove the icon
rm -f icon.png
