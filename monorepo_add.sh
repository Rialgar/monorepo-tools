#!/usr/bin/env bash

# Add repositories to a monorepo from specified remotes
# You must first add the remotes by "git remote add <remote-name> <repository-url>" and fetch from them by "git fetch --all"
# It will merge master branches of the monorepo and all remotes together while keeping all current branches in monorepo intact
# If subdirectory is not specified remote name will be used instead
#
# Usage: monorepo_add.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...
#
# Example: monorepo_add.sh additional-repository package-gamma:packages/gamma package-delta:packages/delta

BRANCHES=""
USE_PREFIX='false'
while getopts 'b:p' flag
do
    case "${flag}" in
        b) BRANCHES="$BRANCHES $OPTARG";;
        p) USE_PREFIX='true';;
    esac
done
shift "$(($OPTIND-1))"


if [ -z "$BRANCHES" ]
then
    BRANCHES="master"
fi

# Check provided arguments
if [ "$#" -lt "1" ]; then
    echo 'Please provide at least 1 remote to be added into an existing monorepo'
    echo 'Usage: monorepo_add.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...'
    echo 'Example: monorepo_add.sh additional-repository package-gamma:packages/gamma package-delta:packages/delta'
    exit
fi

if [ "$USE_PREFIX" = 'true' ] && [ "$#" -gt "1" ]; then
        echo 'Prefixing tags only works when adding one new repository at a time'
        echo 'Make sure only the tags of the new repository are available locally and add each individually'
        exit
    fi
fi

echo "Will merge these branches from the specified remotes: $BRANCHES"

# Get directory of the other scripts
MONOREPO_SCRIPT_DIR=$(dirname "$0")
# Wipe original refs (possible left-over back-up after rewriting git history)
$MONOREPO_SCRIPT_DIR/original_refs_wipe.sh

REMOTES=""
for PARAM in $@; do
    # Parse parameters in format <remote-name>[:<subdirectory>]
    PARAM_ARR=(${PARAM//:/ })
    REMOTE=${PARAM_ARR[0]}
    SUBDIRECTORY=${PARAM_ARR[1]}
    if [ "$SUBDIRECTORY" == "" ]; then
        SUBDIRECTORY=$REMOTE
    fi
    echo "Building branches '$BRANCHES' of the remote '$REMOTE'"    
    REMOTES="$REMOTES $REMOTE"
    REFLIST="";
    for BRANCH in $BRANCHES; do
        git checkout --detach $REMOTE/$BRANCH
        git checkout -b "monorepo_temp/$REMOTE/$BRANCH"
        REFLIST="$REFLIST monorepo_temp/$REMOTE/$BRANCH"
    done
    if [ "$USE_PREFIX" = 'true' ]; then
        $MONOREPO_SCRIPT_DIR/rewrite_history_into.sh -p $REMOTE $SUBDIRECTORY $REFLIST
    else
        $MONOREPO_SCRIPT_DIR/rewrite_history_into.sh $SUBDIRECTORY $REFLIST
    fi

    # Wipe the back-up of original history
    $MONOREPO_SCRIPT_DIR/original_refs_wipe.sh
done

# Merge all requested branches
for BRANCH in $BRANCHES; do
    git checkout $BRANCH
    for REMOTE in $REMOTES; do                
        COMMIT_MSG="merge multiple repositories into a monorepo"$'\n'$'\n'"- merged using: 'monorepo_build.sh $@'"$'\n'"- see https://github.com/shopsys/monorepo-tools"
        echo "Merging $REMOTE/$BRANCH into $BRANCH"
        echo "git merge -q monorepo_temp/$REMOTE/$BRANCH --allow-unrelated-histories -m '$COMMIT_MSG'"
        git merge -q monorepo_temp/$REMOTE/$BRANCH --allow-unrelated-histories -m "$COMMIT_MSG"
        git reset --hard
    done
done