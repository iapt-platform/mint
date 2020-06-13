import { addDecorator } from '@storybook/react';
import { withContexts } from '@storybook/addon-contexts/react';
import { contexts } from './configs/contexts'; // we will define the contextual setups later in API section

addDecorator(withContexts(contexts));
