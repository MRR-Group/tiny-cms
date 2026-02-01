#!/bin/sh

COMPONENTS_DIR="src/components"
MISSING_STORIES=""

# Helper to show path
relative_path() {
    echo "$1" | sed "s|^$COMPONENTS_DIR/||"
}

for dir in $(find "$COMPONENTS_DIR" -type d); do
    # Skip the base components directory itself
    if [ "$dir" = "$COMPONENTS_DIR" ]; then continue; fi

    # Check if there's any .tsx file that is NOT a story or test in this directory
    # This identifies "component" directories in the "one folder, one component" pattern
    if ls "$dir"/*.tsx 2>/dev/null | grep -vE "\.stories\.tsx$|\.test\.tsx$" >/dev/null 2>&1; then
        # It's a component directory, it must have a story
        if ! ls "$dir"/*.stories.tsx >/dev/null 2>&1; then
            COMPONENT_NAME=$(basename "$dir")
            MISSING_STORIES="$MISSING_STORIES $COMPONENT_NAME ($(relative_path "$dir"))"
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
