export function tone(text: string): "green" | "amber" | "red" | "sky" | "neutral" {
	const s = String(text).toLowerCase();
	if (/(complete|success|active|paid|approved)/.test(s)) return "green";
	if (/(pending|processing|warning|hold|reduced)/.test(s)) return "amber";
	if (/(fail|cancel|error|abandon|refund|exempt)/.test(s)) return "red";
	if (/(info|new)/.test(s)) return "sky";
	return "neutral";
}
