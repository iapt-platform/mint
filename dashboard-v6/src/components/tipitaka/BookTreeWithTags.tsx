import { useEffect, useState, type Key } from "react";
import BookTree from "./BookTree";
import { get } from "../../request";
import type { ISearchView } from "../../api/search";
import type { IFtsData, IFtsResponse } from "../../api/fts";

export interface ITagCount {
  name: string;
  count: number;
}

interface IWidget {
  keyWord?: string;
  keyWords?: string[];
  root?: string;
  path?: string[];
  view?: ISearchView;
  multiSelect?: boolean;
  multiSelectable?: boolean;
  onChange?: (selectedKeys: Key[], path?: string[]) => void;
}
const BookTreeWithTags = ({
  keyWord,
  keyWords,
  root = "default",
  path,
  view,
  multiSelect = false,
  multiSelectable = true,
  onChange,
}: IWidget) => {
  const [books, setBooks] = useState<IFtsData[]>();
  useEffect(() => {
    let words;
    let api = "";
    if (keyWord?.trim().includes(" ")) {
      api = "search-book-list";
      words = keyWord;
    } else {
      api = "search-pali-wbw-books";
      words = keyWords?.join();
    }

    const url = `/api/v2/${api}?view=${view}&key=${words}`;
    console.info("api request", url);
    get<IFtsResponse>(url).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        setBooks(json.data.rows);
      }
    });
  }, [keyWord, keyWords, view]);
  return (
    <BookTree
      multiSelect={multiSelect}
      multiSelectable={multiSelectable}
      root={root}
      path={path}
      books={books}
      onChange={onChange}
    />
  );
};

export default BookTreeWithTags;
