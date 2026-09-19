# UI/UX Conventions

## Component vs Plain HTML

**Always prefer a shadcn component first.** Only fall back to plain HTML/Tailwind when no shadcn component exists for that use case.

Examples:
- Badge, Button, Input, Label, Card, Dialog, Separator → shadcn
- Simple text block, paragraph, span → plain HTML (no shadcn equivalent)
- Link to another page → `<Link>` or `<a>` (not wrapped in Button unless it's an action)
- Heading + description → plain `<h1>`/`<p>` (not CardTitle/CardDescription unless inside a Card)
- Container for layout → plain `<div>` with Tailwind (not Card unless it has a header/content structure)

## Page vs Dialog

- **Create/Edit/Delete a single item** → Dialog (inline, no navigation)
- **Viewing a list with actions** → Full page with inline action dialogs
- **Multi-step or complex forms** → Full page
- **Simple confirmations** → AlertDialog

## Forms

- Use shadcn `Label` + `Input` + `Button` — never raw `<label>` tags
- Use `InputError` (not FormMessage) outside react-hook-form context
- Validation errors: `InputError` for auth/settings forms, `FormMessage` only inside `FormField` with react-hook-form

## Layout

- Card for content grouping with header → `<Card><CardHeader><CardTitle>...</CardTitle></CardHeader><CardContent>...</CardContent></Card>`
- Simple container → plain `<div>` with Tailwind
