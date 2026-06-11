import React from "react";

interface SparklineProps {
  data: number[];
  w?: number;
  h?: number;
  color?: string;
}

export function Sparkline({ data, w = 84, h = 28, color = "var(--accent)" }: SparklineProps) {
  const max = Math.max(...data, 1);
  const min = Math.min(...data, 0);

  const pts = data
    .map((d, i) => {
      const x = (i / (data.length - 1)) * w;
      const y = h - ((d - min) / (max - min || 1)) * (h - 4) - 2;
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    })
    .join(" ");

  const area = `0,${h} ${pts} ${w},${h}`;
  const id = "sg" + Math.round(data[0] * 1000) + data.length;

  return (
    <svg
      className="fp-spark"
      width={w}
      height={h}
      viewBox={`0 0 ${w} ${h}`}
    >
      <defs>
        <linearGradient id={id} x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stopColor={color} stopOpacity={0.22} />
          <stop offset="100%" stopColor={color} stopOpacity={0} />
        </linearGradient>
      </defs>
      <polygon points={area} fill={`url(#${id})`} />
      <polyline
        points={pts}
        fill="none"
        stroke={color}
        strokeWidth={1.8}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}
