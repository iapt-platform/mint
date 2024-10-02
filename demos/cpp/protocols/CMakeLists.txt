file(GLOB HEADER_LIST CONFIGURE_DEPENDS "${PROJECT_SOURCE_DIR}/loquat/cpp/*.h")
file(GLOB SOURCE_LIST CONFIGURE_DEPENDS "${PROJECT_SOURCE_DIR}/loquat/cpp/*.cc")

add_library(loquat STATIC ${SOURCE_LIST})

target_include_directories(loquat PUBLIC
  ${mint_SOURCE_DIR}/loquat/cpp/
)

target_link_libraries(loquat PUBLIC
  dl crypt m
  ${CONAN_LIBS}
)

source_group(
  TREE "${PROJECT_SOURCE_DIR}/loquat/cpp"
  PREFIX "Header Files"
  FILES ${HEADER_LIST}
)
