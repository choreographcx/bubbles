# Bubbles Pets Rescue — you are on a stale branch

This branch (`claude/website-files-setup-tzypht`) is early scaffolding from the
first setup session. It is still GitHub's **default** branch, so fresh clones
and new containers land here by mistake.

**All current work lives on `main`**, where the repository root is the site's
`wp-content` directory. Switch before doing anything:

```bash
git fetch origin main && git checkout main
```

`main` carries the real project memory: `CLAUDE.md`, `.claude/rules/` and
`.claude/skills/`.

The user has been advised to change the default branch to `main` in GitHub
Settings; once that happens this file can be deleted.
