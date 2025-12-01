#!/bin/bash

# Script untuk pull dari GitHub di server SSH
# Mengatasi konflik dengan stash perubahan lokal

echo "Checking git status..."
git status

echo ""
echo "Stashing local changes..."
git stash push -m "Local changes before pull - $(date)"

echo ""
echo "Pulling from origin/main..."
git pull origin main

echo ""
echo "Applying stashed changes..."
git stash pop

echo ""
echo "Done! Check for any conflicts above."

