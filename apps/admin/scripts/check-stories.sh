#!/bin/sh

COMPONENTS_DIR="src/components"
MISSING_STORIES=""

for dir in "$COMPONENTS_DIR"/*; do
    if [ -d "$dir" ]; then
        COMPONENT_NAME=$(basename "$dir")
        # Check if there's a .stories.tsx file in the directory
        if ! ls "$dir"/*.stories.tsx >/dev/null 2>&1; then
            MISSING_STORIES="$MISSING_STORIES $COMPONENT_NAME"
        fi
    fi
done

if [ -n "$MISSING_STORIES" ]; then
    echo "❌ Missing Storybook stories for components:$MISSING_STORIES"
    exit 1
else
    echo "✅ All components have Storybook stories."
    exit 0
fi
