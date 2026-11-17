import { useState } from "react";
import { Popover, Skeleton, Typography } from "antd";
import { useIntl } from "react-intl";

import { get as getLang } from "../../locales";
import { fetchTerm, lookupTerms, type Term } from "../../api/Term";
import { useAppSelector } from "../../hooks";
import { getGrammar } from "../../reducers/term-vocabulary";
import MarkupView from "../markup/MarkupView";
import type { MarkupNode } from "../markup/types";

const { Link, Paragraph, Title } = Typography;

/** 语法术语所在的系统 channel 名：_System_Grammar_Term_{语言}_ */
const GRAMMAR_CHANNEL = "_System_Grammar_Term_";

/** 本次会话内的缓存：词_语言 → 术语（找不到记 null） */
const cache = new Map<string, Term | null>();

/**
 * 按词找语法术语（v3，取代 v2 grammar-guide）：
 * 1. 启动时已加载的语法术语表（/v3/glossaries/grammar）里按词找到 id，取 /v3/terms/{id}
 * 2. 表里没有（还没加载完，或表里本就没有）就按词查 /v3/terms?word=，只认语法术语系统 channel 的，
 *    界面语言的优先、其次英文（与 v2 一样回退到英文）
 */
const findGrammarTerm = async (
  word: string,
  grammarIds: Map<string, string>,
): Promise<Term | null> => {
  const id = grammarIds.get(word);
  if (id) {
    return (await fetchTerm(id, {}, true)) ?? null;
  }
  const lang = getLang().toLowerCase();
  const rows =
    (await lookupTerms({ word, with_body: true, per_page: 50 }, true)).data ??
    [];
  const inChannel = (suffix: string) =>
    rows.find((row) => row.channel?.name === `${GRAMMAR_CHANNEL}${suffix}_`);
  return inChannel(lang) ?? inChannel("en") ?? null;
};

interface IWidget {
  text: string;
  gid: string;
}
const GrammarPopWidget = ({ text, gid }: IWidget) => {
  const intl = useIntl();
  const grammar = useAppSelector(getGrammar);
  const cacheKey = `${gid}_${getLang()}`;
  const [term, setTerm] = useState<Term | null | undefined>(
    cache.get(cacheKey),
  );

  const handleMouseEnter = () => {
    if (cache.has(cacheKey)) {
      setTerm(cache.get(cacheKey));
      return;
    }
    const ids = new Map(
      (grammar ?? [])
        .filter((item) => item.guid)
        .map((item) => [item.word, item.guid as string]),
    );
    findGrammarTerm(gid, ids)
      .then((found) => {
        cache.set(cacheKey, found);
        setTerm(found);
      })
      // 悬停是轻量操作：查询失败不弹全局提示（已用 quiet 调用），浮层里显示「暂无说明」
      .catch(() => setTerm(null));
  };

  return (
    <Popover
      content={
        <Paragraph style={{ maxWidth: 500, minWidth: 300, margin: 0 }}>
          {term === undefined ? (
            <Skeleton active title={false} paragraph={{ rows: 2 }} />
          ) : term === null ? (
            <Typography.Text type="secondary">
              {intl.formatMessage(
                { id: "term.grammar.not_found" },
                { word: gid },
              )}
            </Typography.Text>
          ) : (
            <>
              <Title level={5}>{term.meaning}</Title>
              <MarkupView
                className="pcd_article"
                nodes={term.body as MarkupNode[] | undefined}
              />
            </>
          )}
        </Paragraph>
      }
      placement="bottom"
    >
      <Link onMouseEnter={handleMouseEnter}>{text}</Link>
    </Popover>
  );
};

interface IWidgetShell {
  props: string;
}
export const GrammarPopShell = ({ props }: IWidgetShell) => {
  const prop = JSON.parse(atob(props)) as IWidget;
  return (
    <>
      <GrammarPopWidget {...prop} />
    </>
  );
};

export default GrammarPopWidget;
