#!/usr/bin/env bash
# SessionStart hook.
# Compare every file in ci-crm-docs/plainfile/ (the IonCube encode source-of-truth)
# against its live counterpart in ci-crm/. If any drift is found — e.g. an
# encode-set file was hand-edited outside Claude — warn at session start so the
# next encoded build doesn't ship stale plaintext.
set -euo pipefail

CRM_ROOT="/opt/lampp/htdocs/ci-crm"
PLAIN_ROOT="/opt/lampp/htdocs/ci-crm-docs/plainfile"

# Nothing to check if the snapshot dir is missing.
[ -d "$PLAIN_ROOT" ] || exit 0

drift=""
missing=""
while IFS= read -r p; do
  rel="${p#"$PLAIN_ROOT"/}"
  live="$CRM_ROOT/$rel"
  if [ ! -f "$live" ]; then
    missing+="  - $rel (missing in ci-crm)\n"
  elif ! cmp -s "$p" "$live"; then
    drift+="  - $rel\n"
  fi
done < <(find "$PLAIN_ROOT" -type f)

[ -z "$drift" ] && [ -z "$missing" ] && exit 0

msg="⚠️  IonCube encode-set is OUT OF SYNC (plainfile/ vs live ci-crm/):\n"
[ -n "$drift" ]   && msg+="\nChanged (plainfile differs from live source):\n$drift"
[ -n "$missing" ] && msg+="\nMissing in ci-crm:\n$missing"
msg+="\nResolve before producing an encoded build: if the ci-crm/ copy is the intended one, copy it over the plainfile/ copy and RE-ENCODE that file."

# printf interprets the \n escapes; jq -Rs slurps the result into a JSON string.
body="$(printf "%b" "$msg" | jq -Rs .)"
printf '{"systemMessage":%s,"hookSpecificOutput":{"hookEventName":"SessionStart","additionalContext":%s}}\n' "$body" "$body"
