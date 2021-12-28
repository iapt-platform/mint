package mint

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
)

type PaliWordIndex struct {
	Id        int    `json:"id" `
	Word      int    `json:"word" `
	BookId    int    `json:"book_id" `
	WordEn    int    `json:"word_en" `
	Count     string `json:"count" `
	Normal    string `json:"normal" `
	Bold      string `json:"bold" `
	IsBase    bool   `json:"is_base" `
	StrLen    string `json:"str_len" `
	CreatedAt time.Time
}

//display a list of all palitexts
func PaliWordIndexsIndex(db *pg.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		book := c.Query("book")
		paragraph := c.Query("paragraph")

		// TODO 补充业务逻辑
		var pali_word_indexs []PaliWordIndex
		err := db.Model(&pali_word_indexs).Where("book_id = ?", book).Where("paragraph = ?", paragraph).Select()
		if err != nil {
			panic(err)
		}

		c.JSON(http.StatusOK, gin.H{
			"status": "sucess",
			"data":   pali_word_indexs,
		})
	}
}
