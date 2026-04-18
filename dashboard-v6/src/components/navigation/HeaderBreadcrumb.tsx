import { Breadcrumb } from "antd";
import { Link, useMatches } from "react-router";

interface Match {
  pathname: string;
  params: Record<string, string>;
  handle?: {
    crumb?: string | ((match: Match) => React.ReactNode);
  };
}

export default function AppBreadcrumb() {
  const matches = useMatches() as Match[];

  const items = matches
    .filter((m) => m.handle?.crumb)
    .map((m, i, arr) => {
      const crumb = m.handle!.crumb!;
      const label = typeof crumb === "function" ? crumb(m) : crumb;

      const isLast = i === arr.length - 1;

      return {
        title: isLast ? label : <Link to={m.pathname}>{label}</Link>,
      };
    });

  return <Breadcrumb items={items} />;
}
