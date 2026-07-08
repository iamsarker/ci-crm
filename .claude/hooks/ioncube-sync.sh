#!/usr/bin/env bash
# PostToolUse(Write|Edit) hook.
# When a file that belongs to the IonCube encode set is edited in ci-crm/,
# mirror it to ci-crm-docs/plainfile/ (the encode source-of-truth) and tell the
# user to re-encode it. Membership = "a copy already exists under plainfile/",
# so the set auto-tracks whatever is currently in plainfile/ (81 files today).
set -euo pipefail

CRM_ROOT="/opt/lampp/htdocs/ci-crm"
PLAIN_ROOT="/opt/lampp/htdocs/ci-crm-docs/plainfile"

# file path from the hook's stdin JSON (Write/Edit put it in tool_input.file_path)
f="$(jq -r '.tool_input.file_path // .tool_response.filePath // empty' 2>/dev/null || true)"
[ -z "$f" ] && exit 0

# absolute-ify (Edit/Write always pass absolute, but be safe)
case "$f" in
  /*) abs="$f" ;;
  *)  abs="$CRM_ROOT/$f" ;;
esac

# must live under ci-crm/
case "$abs" in
  "$CRM_ROOT"/*) rel="${abs#"$CRM_ROOT"/}" ;;
  *) exit 0 ;;
esac

dest="$PLAIN_ROOT/$rel"

# only mirror files already in the encode set (plainfile counterpart exists)
[ -f "$dest" ] || exit 0

# skip if unchanged (e.g. formatter no-op re-save)
cmp -s "$abs" "$dest" && exit 0

cp -f "$abs" "$dest"

jq -cn --arg rel "$rel" '{
  systemMessage: ("🔒 IonCube encode-set file changed: " + $rel +
    "\n→ mirrored to ci-crm-docs/plainfile/. RE-ENCODE this file before shipping the encoded build.")
}'
