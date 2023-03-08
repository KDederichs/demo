import * as React from 'react';
import {
  useTranslate,
  Edit,
  SimpleForm,
  BooleanInput,
  ReferenceInput,
  AutocompleteInput, useDataProvider,
} from 'react-admin';
import { Box, Grid } from '@mui/material';
import {EditGuesser, InputGuesser} from '@api-platform/admin';
import {ReactQueryDevtools} from "react-query/devtools";

const ReviewEdit = (props) => {
  const translate = useTranslate();
  return (
    <EditGuesser {...props}>
      <InputGuesser source={"body"} />
      <InputGuesser source={"rating"} />
      <ReferenceInput
        source="selfRef"
        reference="reviews"
        label="Self Ref"
        // filter={{
        //   roles: [
        //     'ROLE_QPAY_SALES',
        //     'ROLE_QPAY_ADMIN',
        //     'ROLE_SUPER_ADMIN',
        //   ],
        // }}
        defaultValue={null}
        filterToQuery={(searchText) => ({ body: searchText })}>
        <AutocompleteInput
          optionText="title"
          defaultValue={null}
          filterToQuery={(searchText) => ({ body: searchText })}
        />
      </ReferenceInput>
      <InputGuesser source={"author"} />
      <InputGuesser source={"publicationDate"} />
    </EditGuesser>
  );
};

export default ReviewEdit;
