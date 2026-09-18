# UI/UX Conventions

## Component vs Plain HTML

Use shadcn components for structured UI (forms, dialogs, tables, cards, badges).
Use plain HTML/Tailwind for simple text, links, and containers — not everything needs a component.

Examples:
- Simple text block, paragraph, span → plain HTML
- Link to another page → `<Link>` or `<a>`, not wrapped in Button
- Heading + description → plain `<h1>`/`<p>`, not CardTitle/CardDescription
- Container for layout → plain `<div>`, not Card

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
